<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\MenuItemBranch;
use App\Http\Requests\MenuItem\StoreMenuItemRequest;
use App\Http\Requests\MenuItem\UpdateMenuItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    /**
     * Menampilkan semua menu (untuk customer: hanya yang aktif).
     */
    public function index(Request $request)
    {
        $query = MenuItem::with('category');

        // Customer hanya lihat menu aktif
        if (!$request->user() || $request->user()->isCustomer()) {
            $query->where('is_active', true);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $menuItems = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar menu berhasil diambil',
            'data' => $menuItems,
        ]);
    }

    /**
     * Menampilkan detail menu beserta ketersediaan per cabang.
     */
    public function show(MenuItem $menuItem)
    {
        $menuItem->load(['category', 'branches']);

        return response()->json([
            'success' => true,
            'message' => 'Detail menu berhasil diambil',
            'data' => $menuItem,
        ]);
    }

    /**
     * Membuat menu baru.
     * Akses: Super Admin only
     */
    public function store(StoreMenuItemRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        // Pastikan slug unik
        $originalSlug = $data['slug'];
        $counter = 1;
        while (MenuItem::withTrashed()->where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter++;
        }

        $data['image_url'] = Storage::disk('s3')->put('menu-images', $request->file('image_url'));

        $menuItem = MenuItem::create($data);
        $menuItem->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan',
            'data' => $menuItem,
        ], 201);
    }

    /**
     * Mengupdate menu.
     * Akses: Super Admin only
     */
    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem)
    {
        $data = $request->validated();

        // Regenerate slug jika nama berubah
        if (isset($data['name'])) {
            $slug = Str::slug($data['name']);
            $originalSlug = $slug;
            $counter = 1;
            while (MenuItem::withTrashed()->where('slug', $slug)->where('id', '!=', $menuItem->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('image_url')) {
            if ($menuItem->image_url) {
                Storage::disk('s3')->delete($menuItem->image_url);
            }
            $data['image_url'] = Storage::disk('s3')->put('menu-images', $request->file('image_url'));
        } else {
            unset($data['image_url']);
        }

        $menuItem->update($data);
        $menuItem->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil diperbarui',
            'data' => $menuItem,
        ]);
    }

    /**
     * Import menu dari file CSV.
     * Akses: Super Admin only
     */
    public function importCSV(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            return response()->json([
                'success' => false,
                'message' => 'File CSV kosong atau format tidak valid',
            ], 400);
        }

        // Expected header format: category, name, description, base_price
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);

        $categoryIndex = array_search('category', $header);
        $nameIndex = array_search('name', $header);
        $descriptionIndex = array_search('description', $header);
        $priceIndex = array_search('base_price', $header);

        if ($categoryIndex === false || $nameIndex === false || $priceIndex === false) {
            return response()->json([
                'success' => false,
                'message' => 'File CSV harus memiliki kolom category, name, dan base_price',
            ], 400);
        }

        if ($categoryIndex >= $nameIndex) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom category harus berada sebelum kolom name',
            ], 400);
        }

        $existingCategories = \App\Models\Category::pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower($name) => $id];
        })->toArray();

        $existingMenus = MenuItem::pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower($name) => $id];
        })->toArray();

        $rows = [];
        $errors = [];
        $csvMenus = [];
        $rowNumber = 2; // header is row 1

        while (($row = fgetcsv($handle)) !== false) {
            $categoryName = trim($row[$categoryIndex] ?? '');
            $name = trim($row[$nameIndex] ?? '');
            $description = $descriptionIndex !== false ? trim($row[$descriptionIndex] ?? '') : null;
            $price = trim($row[$priceIndex] ?? '');

            // Validate Category
            if (empty($categoryName)) {
                $errors[] = "Baris $rowNumber: Kategori kosong.";
            } elseif (!array_key_exists(strtolower($categoryName), $existingCategories)) {
                $errors[] = "Baris $rowNumber: Kategori '{$categoryName}' tidak ditemukan di sistem.";
            }

            // Validate Name
            if (empty($name)) {
                $errors[] = "Baris $rowNumber: Nama menu kosong.";
            } else {
                $lowerName = strtolower($name);
                if (array_key_exists($lowerName, $existingMenus)) {
                    $errors[] = "Baris $rowNumber: Menu '{$name}' sudah ada di sistem.";
                } elseif (in_array($lowerName, $csvMenus)) {
                    $errors[] = "Baris $rowNumber: Menu '{$name}' duplikat di dalam file CSV.";
                } else {
                    $csvMenus[] = $lowerName;
                }
            }

            // Validate Price
            if (!is_numeric($price)) {
                $errors[] = "Baris $rowNumber: Harga dasar '{$price}' tidak valid.";
            }

            $rows[] = [
                'category_id' => $existingCategories[strtolower($categoryName)] ?? null,
                'name' => $name,
                'description' => $description,
                'base_price' => $price,
            ];

            $rowNumber++;
        }
        fclose($handle);

        if (count($errors) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat kesalahan validasi pada file CSV.',
                'errors' => $errors,
            ], 400);
        }

        $importedCount = 0;
        foreach ($rows as $rowData) {
            $slug = Str::slug($rowData['name']);
            $originalSlug = $slug;
            $counter = 1;
            while (MenuItem::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . time() . rand(10, 99);
            }

            MenuItem::create([
                'category_id' => $rowData['category_id'],
                'name' => $rowData['name'],
                'slug' => $slug,
                'description' => empty($rowData['description']) ? null : $rowData['description'],
                'base_price' => $rowData['base_price'],
                'is_active' => true,
                'image_url' => null,
            ]);

            $importedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Import berhasil. {$importedCount} menu ditambahkan.",
            'imported_count' => $importedCount,
        ]);
    }

    /**
     * Export semua menu ke file CSV.
     * Akses: Super Admin only
     */
    public function exportCSV(Request $request)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="menus_export_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            
            // Header CSV (sesuai format import)
            fputcsv($file, ['category', 'name', 'description', 'base_price']);

            // Ambil semua menu (aktif & non-aktif) beserta kategorinya
            $menus = MenuItem::with('category')->get();

            foreach ($menus as $menu) {
                fputcsv($file, [
                    $menu->category ? $menu->category->name : '',
                    $menu->name,
                    $menu->description,
                    $menu->base_price,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Menghapus menu (soft delete).
     * Akses: Super Admin only
     */
    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil dihapus',
        ]);
    }
}
