<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

/**
 * @group Manajemen About
 *
 * Endpoint API untuk mengelola data biografi, statistik pengalaman, foto profil, dan email kontak.
 */
class AboutController extends Controller
{
    /**
     * Dapatkan Data About (Baris Pertama)
     * Mengembalikan status 200 meskipun data di database masih kosong (mencegah error 500)
     */
    public function index()
    {
        try {
            $about = About::first();

            // Jika data di database belum ada, kembalikan objek default dengan status 200
            if (!$about) {
                return response()->json([
                    'message' => 'Data about belum ada, menampilkan struktur default',
                    'data' => [
                        'bio' => '',
                        'years_experience' => '0',
                        'projects_completed' => '0',
                        'contact_email' => '',
                        'profile_image_url' => null,
                        'image_url' => null,
                    ]
                ], 200);
            }

            return response()->json([
                'message' => 'Data about berhasil diambil',
                'data' => $about
            ], 200);
        } catch (\Throwable $e) {
            // Tangani gracefully jika tabel belum di-migrate agar frontend tidak crash dengan error 500
            return response()->json([
                'message' => 'Data about belum tersedia',
                'data' => [
                    'bio' => '',
                    'years_experience' => '0',
                    'projects_completed' => '0',
                    'contact_email' => '',
                    'profile_image_url' => null,
                    'image_url' => null,
                ]
            ], 200);
        }
    }

    /**
     * Perbarui atau Simpan Data About (Baris Pertama)
     * Menggunakan updateOrCreate agar tidak error jika data belum ada di database
     *
     * @authenticated
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'bio' => 'nullable|string',
            'years_experience' => 'nullable|string|max:50',
            'projects_completed' => 'nullable|string|max:50',
            'contact_email' => 'nullable|string|email|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'profile_image_url' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:1000',
        ]);

        // Upload foto profil (ke Cloudinary / lokal) tertangani dengan aman
        if ($request->hasFile('profile_image')) {
            try {
                $uploadedUrl = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                    'folder' => 'profile'
                ])->getSecurePath();
                $validated['profile_image_url'] = $uploadedUrl;
            } catch (\Throwable $e) {
                // Fallback jika Cloudinary tidak terkonfigurasi: simpan ke storage public lokal
                $path = $request->file('profile_image')->store('profiles', 'public');
                $validated['profile_image_url'] = asset('storage/' . $path);
            }
            unset($validated['profile_image']);
        } elseif ($request->hasFile('image')) {
            try {
                $uploadedUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                    'folder' => 'profile'
                ])->getSecurePath();
                $validated['profile_image_url'] = $uploadedUrl;
            } catch (\Throwable $e) {
                $path = $request->file('image')->store('profiles', 'public');
                $validated['profile_image_url'] = asset('storage/' . $path);
            }
            unset($validated['image']);
        } elseif ($request->filled('profile_image_url')) {
            $validated['profile_image_url'] = $request->input('profile_image_url');
        } elseif ($request->filled('image_url')) {
            $validated['profile_image_url'] = $request->input('image_url');
        }

        // Cari baris pertama yang ada di database
        $firstAbout = About::first();

        // Gunakan updateOrCreate untuk menyimpan data dengan aman
        $about = About::updateOrCreate(
            ['id' => $firstAbout ? $firstAbout->id : 1],
            [
                'bio' => $validated['bio'] ?? ($firstAbout->bio ?? ''),
                'years_experience' => $validated['years_experience'] ?? ($firstAbout->years_experience ?? '0'),
                'projects_completed' => $validated['projects_completed'] ?? ($firstAbout->projects_completed ?? '0'),
                'contact_email' => $validated['contact_email'] ?? ($firstAbout->contact_email ?? ''),
                'profile_image_url' => $validated['profile_image_url'] ?? ($firstAbout->profile_image_url ?? null),
            ]
        );

        return response()->json([
            'message' => 'Data About berhasil disimpan',
            'data' => $about
        ], 200);
    }
}
