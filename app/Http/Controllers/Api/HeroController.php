<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hero;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

/**
 * @group Manajemen Hero
 *
 * Endpoint API untuk mengelola konten salam hero, deskripsi, marquee, dan gambar hero.
 */
class HeroController extends Controller
{
    /**
     * Dapatkan Data Hero (Baris Pertama)
     */
    public function index()
    {
        $hero = Hero::first();

        return response()->json([
            'message' => 'Data hero berhasil diambil',
            'data' => $hero
        ], 200);
    }

    /**
     * Perbarui atau Simpan Data Hero (Baris Pertama)
     *
     * @authenticated
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'greeting' => 'required|string|max:255',
            'description' => 'required|string',
            'marquee_text' => 'required|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url' => 'nullable|string|max:1000',
        ]);

        // Upload gambar hero ke Cloudinary jika ada file baru
        if ($request->hasFile('image')) {
            $uploadedUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'hero'
            ])->getSecurePath();
            $validated['image_url'] = $uploadedUrl;
            unset($validated['image']);
        } elseif ($request->hasFile('image_url')) {
            $uploadedUrl = Cloudinary::upload($request->file('image_url')->getRealPath(), [
                'folder' => 'hero'
            ])->getSecurePath();
            $validated['image_url'] = $uploadedUrl;
        }

        $hero = Hero::first();

        if ($hero) {
            $hero->update($validated);
        } else {
            $hero = Hero::create($validated);
        }

        return response()->json([
            'message' => 'Konten Hero berhasil diperbarui',
            'data' => $hero
        ], 200);
    }
}

