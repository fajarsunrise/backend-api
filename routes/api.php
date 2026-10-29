<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ArticleController;

// Endpoint Publik (Bisa diakses siapa saja)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Endpoint Privat (Hanya bisa diakses jika memiliki token yang valid)
Route::middleware('auth:sanctum')->get('/profil', function (Request $request) {
    return response()->json([
        'message' => 'Ini adalah data rahasia user',
        'data' => $request->user()
    ]);
});

// Route yang dilindungi
Route::middleware('auth:sanctum')->group(function () {

    // Rute profil sebelumnya
    Route::get('/profil', function (Request $request) {
        return $request->user();
    });

    // Mendaftarkan 5 rute CRUD sekaligus
    Route::apiResource('articles', ArticleController::class);
    });


