<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\UserVoucher;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    // ==========================================
    // CUSTOMER ROUTES
    // ==========================================

    /**
     * List semua voucher yang aktif dan bisa ditukar.
     */
    public function index()
    {
        $vouchers = Voucher::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar voucher berhasil diambil',
            'data' => $vouchers,
        ]);
    }

    /**
     * Menukar poin loyalty dengan voucher.
     */
    public function exchange(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
        ]);

        $user = $request->user();
        $voucher = Voucher::where('id', $request->voucher_id)
            ->where('is_active', true)
            ->firstOrFail();

        if ($user->loyalty_points < $voucher->points_required) {
            throw ValidationException::withMessages([
                'points' => ['Poin loyalty Anda tidak mencukupi untuk menukar voucher ini.'],
            ]);
        }

        // Kurangi poin
        $user->decrement('loyalty_points', $voucher->points_required);

        // Tambahkan ke user_vouchers (expired 30 hari dari sekarang)
        $userVoucher = UserVoucher::create([
            'user_id' => $user->id,
            'voucher_id' => $voucher->id,
            'is_used' => false,
            'expired_at' => now()->addDays(30),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menukar poin dengan voucher diskon',
            'data' => $userVoucher->load('voucher'),
        ]);
    }

    /**
     * List voucher yang dimiliki user.
     */
    public function myVouchers(Request $request)
    {
        $userVouchers = UserVoucher::where('user_id', $request->user()->id)
            ->with('voucher')
            ->orderBy('is_used', 'asc') // Belum digunakan di atas
            ->orderBy('expired_at', 'asc') // Yang mau expired di atas
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar voucher Anda berhasil diambil',
            'data' => $userVouchers,
        ]);
    }

    // ==========================================
    // ADMIN ROUTES (CRUD)
    // ==========================================

    /**
     * Tambah voucher baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:vouchers,code|max:50',
            'discount_amount' => 'required|numeric|min:0',
            'min_transaction_amount' => 'required|numeric|min:0',
            'points_required' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $voucher = Voucher::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil dibuat',
            'data' => $voucher,
        ], 201);
    }

    /**
     * Update voucher.
     */
    public function update(Request $request, Voucher $voucher)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:vouchers,code,' . $voucher->id,
            'discount_amount' => 'sometimes|numeric|min:0',
            'min_transaction_amount' => 'sometimes|numeric|min:0',
            'points_required' => 'sometimes|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $voucher->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil diupdate',
            'data' => $voucher,
        ]);
    }

    /**
     * Hapus voucher.
     */
    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil dihapus',
        ]);
    }
}
