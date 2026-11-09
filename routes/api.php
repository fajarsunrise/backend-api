<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TagController;

// ==========================================
// 1. ENDPOINT PUBLIK (Bisa diakses dari React tanpa login)
// ==========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Mengizinkan React membaca data (index & show) untuk ditampilkan di portofolio
Route::apiResource('articles', ArticleController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('tags', TagController::class)->only(['index', 'show']);

Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    return response()->json(['message' => 'Cache berhasil dibersihkan! Silakan coba upload gambar lagi.']);
});

// ==========================================
// 2. ENDPOINT PRIVAT (Ruang Admin - Membutuhkan Token Sanctum)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profil', function (Request $request) {
        return response()->json([
            'message' => 'Ini adalah data rahasia user',
            'data' => $request->user()
        ]);
    });

    // Mengunci akses Tambah (store), Ubah (update), dan Hapus (destroy)
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    Route::apiResource('tags', TagController::class)->except(['index', 'show']);

});
