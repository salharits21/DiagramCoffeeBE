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

        $data['image_url'] = Storage::disk('public')->put('menu-images', $request->file('image_url'));

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
                Storage::disk('public')->delete($menuItem->image_url);
            }
            $data['image_url'] = Storage::disk('public')->put('menu-images', $request->file('image_url'));
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

        // Expected header format: name, description, base_price
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);

        $nameIndex = array_search('name', $header);
        $descriptionIndex = array_search('description', $header);
        $priceIndex = array_search('base_price', $header);

        if ($nameIndex === false || $priceIndex === false) {
            return response()->json([
                'success' => false,
                'message' => 'File CSV harus memiliki kolom name dan base_price',
            ], 400);
        }

        $importedCount = 0;
        $failedCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[$nameIndex] ?? '');
            $description = $descriptionIndex !== false ? trim($row[$descriptionIndex] ?? '') : null;
            $price = trim($row[$priceIndex] ?? '');

            if (empty($name) || !is_numeric($price)) {
                $failedCount++;
                continue;
            }

            $slug = Str::slug($name);
            $originalSlug = $slug;
            $counter = 1;
            while (MenuItem::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . time() . rand(10, 99);
            }

            MenuItem::create([
                'category_id' => null,
                'name' => $name,
                'slug' => $slug,
                'description' => empty($description) ? null : $description,
                'base_price' => $price,
                'is_active' => true,
                'image_url' => null,
            ]);

            $importedCount++;
        }

        fclose($handle);

        return response()->json([
            'success' => true,
            'message' => "Import berhasil. {$importedCount} menu ditambahkan, {$failedCount} baris gagal diimpor.",
            'imported_count' => $importedCount,
            'failed_count' => $failedCount,
        ]);
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
