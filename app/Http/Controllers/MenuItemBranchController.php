<?php

namespace App\Http\Controllers;

use App\Models\MenuItemBranch;
use App\Models\Branch;
use App\Models\MenuItem;
use App\Http\Requests\MenuItemBranch\UpdateStockRequest;
use Illuminate\Http\Request;

class MenuItemBranchController extends Controller
{
    /**
     * Menampilkan semua menu di cabang tertentu beserta stok & promo.
     * Akses: Super Admin, Admin (cabangnya sendiri)
     */
    public function index(Request $request, Branch $branch)
    {
        $user = $request->user();

        // Admin hanya bisa lihat cabangnya sendiri
        if ($user->isAdmin() && $user->branch_id !== $branch->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat mengakses data cabang Anda sendiri',
            ], 403);
        }

        $menuItemBranches = MenuItemBranch::where('branch_id', $branch->id)
            ->with(['menuItem.category'])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data stok menu cabang berhasil diambil',
            'data' => $menuItemBranches,
        ]);
    }

    /**
     * Assign menu item ke cabang (buat record pivot baru).
     * Akses: Super Admin only
     */
    public function assign(Request $request, Branch $branch, MenuItem $menuItem)
    {
        // Cek apakah sudah di-assign
        $existing = MenuItemBranch::where('branch_id', $branch->id)
            ->where('menu_item_id', $menuItem->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Menu sudah tersedia di cabang ini',
            ], 409);
        }

        $menuItemBranch = MenuItemBranch::create([
            'menu_item_id' => $menuItem->id,
            'branch_id' => $branch->id,
            'is_available' => true,
        ]);

        $menuItemBranch->load(['menuItem.category', 'branch']);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan ke cabang',
            'data' => $menuItemBranch,
        ], 201);
    }

    /**
     * Update stok, ketersediaan, dan promo menu di cabang.
     * Akses: Super Admin (semua cabang), Admin (cabangnya sendiri)
     */
    public function update(UpdateStockRequest $request, Branch $branch, MenuItem $menuItem)
    {
        $user = $request->user();

        // Admin hanya bisa update cabangnya sendiri
        if ($user->isAdmin() && $user->branch_id !== $branch->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat mengupdate stok cabang Anda sendiri',
            ], 403);
        }

        $menuItemBranch = MenuItemBranch::where('branch_id', $branch->id)
            ->where('menu_item_id', $menuItem->id)
            ->first();

        if (!$menuItemBranch) {
            return response()->json([
                'success' => false,
                'message' => 'Menu tidak ditemukan di cabang ini',
            ], 404);
        }

        $data = $request->validated();

        // Jika discount_type berubah, reset field yang tidak relevan
        if (isset($data['discount_type'])) {
            if ($data['discount_type'] === 'percentage') {
                $data['discount_amount'] = null;
            } elseif ($data['discount_type'] === 'fixed') {
                $data['discount_percentage'] = null;
            }
        }

        // Jika discount_type di-null-kan, reset semua diskon
        if (array_key_exists('discount_type', $data) && $data['discount_type'] === null) {
            $data['discount_percentage'] = null;
            $data['discount_amount'] = null;
            $data['is_promo_active'] = false;
        }

        $menuItemBranch->update($data);
        $menuItemBranch->load(['menuItem.category', 'branch']);

        return response()->json([
            'success' => true,
            'message' => 'Stok menu berhasil diperbarui',
            'data' => $menuItemBranch,
        ]);
    }

    /**
     * Hapus menu dari cabang (unassign).
     * Akses: Super Admin only
     */
    public function unassign(Branch $branch, MenuItem $menuItem)
    {
        $menuItemBranch = MenuItemBranch::where('branch_id', $branch->id)
            ->where('menu_item_id', $menuItem->id)
            ->first();

        if (!$menuItemBranch) {
            return response()->json([
                'success' => false,
                'message' => 'Menu tidak ditemukan di cabang ini',
            ], 404);
        }

        $menuItemBranch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil dihapus dari cabang',
        ]);
    }
}
