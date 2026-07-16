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
    public function assign(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'menu_item_ids' => 'required|array',
            'menu_item_ids.*' => 'exists:menu_items,id',
        ]);

        $menuItemIds = $validated['menu_item_ids'];
        $assignedItems = [];
        $skippedIds = [];

        foreach ($menuItemIds as $menuItemId) {
            // Cek apakah sudah di-assign
            $existing = MenuItemBranch::where('branch_id', $branch->id)
                ->where('menu_item_id', $menuItemId)
                ->first();

            if ($existing) {
                $skippedIds[] = $menuItemId;
                continue;
            }

            $menuItemBranch = MenuItemBranch::create([
                'menu_item_id' => $menuItemId,
                'branch_id' => $branch->id,
                'is_available' => true,
            ]);

            $menuItemBranch->load(['menuItem.category', 'branch']);
            $assignedItems[] = $menuItemBranch;
        }

        return response()->json([
            'success' => true,
            'message' => count($assignedItems) . ' Menu berhasil ditambahkan ke cabang' . (count($skippedIds) > 0 ? ', ' . count($skippedIds) . ' dilewati (sudah ada)' : ''),
            'data' => $assignedItems,
            'skipped_ids' => $skippedIds,
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
    public function unassign(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'menu_item_ids' => 'required|array',
            'menu_item_ids.*' => 'exists:menu_items,id',
        ]);

        $menuItemIds = $validated['menu_item_ids'];

        $deletedCount = MenuItemBranch::where('branch_id', $branch->id)
            ->whereIn('menu_item_id', $menuItemIds)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => $deletedCount . ' Menu berhasil dihapus dari cabang',
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Copy menu items dari cabang lain ke cabang ini.
     * Akses: Super Admin only
     */
    public function copy(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'source_branch_id' => 'required|exists:branches,id|different:' . $branch->id,
            'overwrite' => 'nullable|boolean',
        ]);

        $sourceBranchId = $validated['source_branch_id'];
        $overwrite = $validated['overwrite'] ?? false;

        $sourceMenus = MenuItemBranch::where('branch_id', $sourceBranchId)->get();

        if ($sourceMenus->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cabang sumber tidak memiliki menu',
            ], 404);
        }

        $copiedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($sourceMenus as $sourceMenu) {
            $existing = MenuItemBranch::where('branch_id', $branch->id)
                ->where('menu_item_id', $sourceMenu->menu_item_id)
                ->first();

            $dataToCopy = [
                'is_available' => $sourceMenu->is_available,
                'stock' => $sourceMenu->stock,
                'discount_type' => $sourceMenu->discount_type,
                'discount_percentage' => $sourceMenu->discount_percentage,
                'discount_amount' => $sourceMenu->discount_amount,
                'is_promo_active' => $sourceMenu->is_promo_active,
            ];

            if ($existing) {
                if ($overwrite) {
                    $existing->update($dataToCopy);
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                MenuItemBranch::create(array_merge([
                    'menu_item_id' => $sourceMenu->menu_item_id,
                    'branch_id' => $branch->id,
                ], $dataToCopy));
                $copiedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyalin menu. $copiedCount disalin, $updatedCount diperbarui, $skippedCount dilewati.",
            'stats' => [
                'copied' => $copiedCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
            ]
        ]);
    }
}
