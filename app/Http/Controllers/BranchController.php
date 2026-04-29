<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;

class BranchController extends Controller
{
    /**
     * Menampilkan semua cabang.
     * Akses: Super Admin, Admin, Customer (publik)
     */
    public function index()
    {
        $branches = Branch::where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar cabang berhasil diambil',
            'data' => $branches,
        ]);
    }

    /**
     * Menampilkan detail cabang.
     */
    public function show(Branch $branch)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail cabang berhasil diambil',
            'data' => $branch,
        ]);
    }

    /**
     * Membuat cabang baru.
     * Akses: Super Admin only
     */
    public function store(StoreBranchRequest $request)
    {
        $branch = Branch::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cabang berhasil ditambahkan',
            'data' => $branch,
        ], 201);
    }

    /**
     * Mengupdate cabang.
     * Akses: Super Admin only
     */
    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cabang berhasil diperbarui',
            'data' => $branch,
        ]);
    }

    /**
     * Menghapus cabang (soft delete).
     * Akses: Super Admin only
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cabang berhasil dihapus',
        ]);
    }

    /**
     * Menampilkan semua cabang termasuk yang inactive (untuk admin panel).
     * Akses: Super Admin only
     */
    public function all()
    {
        $branches = Branch::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Semua cabang berhasil diambil',
            'data' => $branches,
        ]);
    }
}
