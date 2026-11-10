<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\ToolController;

// ==========================================
// 1. ENDPOINT PUBLIK (Bisa diakses dari React tanpa login)
// ==========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Mengizinkan React membaca data (index & show) untuk ditampilkan di portofolio
Route::apiResource('articles', ArticleController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('tags', TagController::class)->only(['index', 'show']);
Route::apiResource('projects', ProjectController::class)->only(['index', 'show']);
Route::apiResource('certificates', CertificateController::class)->only(['index', 'show']);
Route::apiResource('tools', ToolController::class)->only(['index', 'show']);
Route::get('/about', [AboutController::class, 'index']);

Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    return response()->json(['message' => 'Cache dan routing berhasil dibersihkan! Silakan coba upload gambar lagi.']);
});

Route::get('/run-migrate', function() {
    Artisan::call('migrate', ['--force' => true]);
    return response()->json([
        'message' => 'Migrasi database berhasil dijalankan!',
        'output' => Artisan::output()
    ]);
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
    Route::apiResource('projects', ProjectController::class)->except(['index', 'show']);
    Route::apiResource('certificates', CertificateController::class)->except(['index', 'show']);
    Route::apiResource('tools', ToolController::class)->except(['index', 'show']);
    Route::post('/about', [AboutController::class, 'update']);

});
