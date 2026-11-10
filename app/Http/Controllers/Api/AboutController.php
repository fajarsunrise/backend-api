<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

/**
 * @group Pengaturan Konten Utama
 *
 * Endpoint API untuk mengelola konten Hero, About, dan Contact.
 */
class AboutController extends Controller
{
    /**
     * Dapatkan Data Konten Utama (Baris Pertama)
     */
    public function index()
    {
        // Ambil baris pertama atau buat data inisial jika tabel masih kosong
        $about = About::first();

        return response()->json([
            'message' => 'Data konten utama berhasil diambil',
            'data' => $about
        ], 200);
    }

    /**
     * Perbarui atau Simpan Data Konten Utama (Baris Pertama)
     *
     * @authenticated
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'hero_greeting' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'marquee_text' => 'nullable|string|max:500',
            'about_bio' => 'nullable|string',
            'years_experience' => 'nullable|string|max:50',
            'projects_completed' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'profile_image_url' => 'nullable|string|max:1000',
            'contact_email' => 'nullable|string|email|max:255',
        ]);

        // Cek jika ada file gambar yang diunggah ke Cloudinary
        if ($request->hasFile('profile_image')) {
            $uploadedUrl = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                'folder' => 'profile'
            ])->getSecurePath();
            $validated['profile_image_url'] = $uploadedUrl;
            unset($validated['profile_image']);
        } elseif ($request->hasFile('profile_image_url')) {
            $uploadedUrl = Cloudinary::upload($request->file('profile_image_url')->getRealPath(), [
                'folder' => 'profile'
            ])->getSecurePath();
            $validated['profile_image_url'] = $uploadedUrl;
        }

        // Ambil baris pertama atau buat baru
        $about = About::first();

        if ($about) {
            $about->update($validated);
        } else {
            $about = About::create($validated);
        }

        return response()->json([
            'message' => 'Pengaturan konten utama berhasil diperbarui',
            'data' => $about
        ], 200);
    }
}

