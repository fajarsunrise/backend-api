<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function registerUser(array $data)
    {
        // Membuat user baru dengan password yang di-hash
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        // Membuat token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function loginUser(array $credentials)
    {
        // Mengecek apakah email dan password cocok
        if (!Auth::attempt($credentials)) {
            return false;
        }

        // Mengambil data user dan membuat token
        $user = User::where('email', $credentials['email'])->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}
