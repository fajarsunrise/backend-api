<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $result = $this->authService->registerUser($validated);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'data' => $result['user'],
            'access_token' => $result['token'],
            'token_type' => 'Bearer'
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $result = $this->authService->loginUser($credentials);

        if (!$result) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $result['token'],
            'token_type' => 'Bearer'
        ]);
    }
}
