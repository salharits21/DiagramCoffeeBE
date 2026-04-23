<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public Routes (Tidak perlu login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Harus membawa Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Endpoint untuk mendapatkan data profile user yang sedang login
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil',
            'data' => $request->user()
        ]);
    });
});
