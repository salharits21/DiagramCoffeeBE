<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    /**
     * Tampilkan semua pengaturan aplikasi.
     */
    public function index()
    {
        $settings = AppSetting::all();

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan aplikasi berhasil diambil',
            'data' => $settings,
        ]);
    }

    /**
     * Tambah fee lain (PPh, dll).
     */
    public function storeFee(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:app_settings,key',
            'value' => 'required|numeric|min:0' . ($request->type === 'percentage' ? '|max:100' : ''),
            'label' => 'required|string',
            'type' => 'required|in:fixed,percentage',
        ]);

        $setting = AppSetting::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Fee berhasil ditambahkan',
            'data' => $setting,
        ], 201);
    }

    /**
     * Update nilai pengaturan berdasarkan key.
     */
    public function updateFee(Request $request, string $key)
    {
        $request->validate([
            'value' => 'required|numeric|min:0' . ($request->type === 'percentage' ? '|max:100' : ''),
            'label' => 'required|string',
            'type' => 'required|in:fixed,percentage',
        ]);

        $setting = AppSetting::where('key', $key)->firstOrFail();

        $setting->update(['value' => $request->value, 'label' => $request->label, 'type' => $request->type]);

        return response()->json([
            'success' => true,
            'message' => 'Fee berhasil diperbarui',
            'data' => $setting,
        ]);
    }

    /**
     * Hapus fee.
     */
    public function deleteFee(string $key)
    {
        $setting = AppSetting::where('key', $key)->firstOrFail();

        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee berhasil dihapus',
            'data' => $setting,
        ]);
    }
}
