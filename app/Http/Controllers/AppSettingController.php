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
     * Update nilai pengaturan berdasarkan key.
     */
    public function update(Request $request, string $key)
    {
        $request->validate([
            'value' => 'required|string',
        ]);

        $setting = AppSetting::where('key', $key)->firstOrFail();

        // Validasi khusus untuk admin_fee
        if ($key === 'admin_fee') {
            $request->validate([
                'value' => 'required|numeric|min:0',
            ]);
        }

        $setting->update(['value' => $request->value]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil diperbarui',
            'data' => $setting,
        ]);
    }
}
