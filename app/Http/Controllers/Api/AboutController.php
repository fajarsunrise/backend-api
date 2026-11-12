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
     * Helper untuk memformat URL gambar profil agar selalu lengkap dan valid
     */
    private function formatImageUrl(?string $imageUrl): ?string
    {
        if (empty($imageUrl)) {
            return null;
        }

        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://') || str_starts_with($imageUrl, 'data:')) {
            return $imageUrl;
        }

        $cleanPath = ltrim(str_replace('storage/', '', $imageUrl), '/');
        return url('storage/' . $cleanPath);
    }

    /**
     * Helper untuk memformat data respons About
     */
    private function formatAboutResponse(?About $about): array
    {
        if (!$about) {
            return [
                'name' => '',
                'bio' => '',
                'gpa' => null,
                'projects_completed' => '0',
                'contact_email' => '',
                'profile_image_url' => null,
                'image_url' => null,
                'avatar_url' => null,
            ];
        }

        $formattedImage = $this->formatImageUrl($about->profile_image_url ?? $about->image_url ?? $about->avatar_url);

        // Jika foto About belum diunggah, gunakan foto dari Hero sebagai fallback
        if (empty($formattedImage)) {
            try {
                $hero = \App\Models\Hero::first();
                if ($hero && !empty($hero->image_url)) {
                    $formattedImage = $this->formatImageUrl($hero->image_url);
                }
            } catch (\Throwable $e) {
                // Abaikan jika tabel/data hero belum tersedia
            }
        }

        return [
            'id' => $about->id,
            'name' => $about->name ?? '',
            'bio' => $about->bio ?? '',
            'gpa' => $about->gpa !== null ? (string)$about->gpa : null,
            'projects_completed' => $about->projects_completed ?? '0',
            'contact_email' => $about->contact_email ?? '',
            'profile_image_url' => $formattedImage,
            'image_url' => $formattedImage,
            'avatar_url' => $formattedImage,
            'created_at' => $about->created_at,
            'updated_at' => $about->updated_at,
        ];
    }

    /**
     * Dapatkan Data About (Baris Pertama)
     * Mengembalikan status 200 meskipun data di database masih kosong (mencegah error 500)
     */
    public function index()
    {
        try {
            $about = About::first();

            return response()->json([
                'message' => 'Data about berhasil diambil',
                'data' => $this->formatAboutResponse($about)
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Data about belum tersedia',
                'data' => $this->formatAboutResponse(null)
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
            'gpa' => 'nullable|numeric|between:0,4.00',
            'projects_completed' => 'nullable|string|max:50',
            'contact_email' => 'nullable|string|email|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'profile_image_url' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:1000',
        ]);

        $firstAbout = About::first();

        // Cari file gambar baru jika ada
        $file = null;
        if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
            $file = $request->file('profile_image');
        } elseif ($request->hasFile('image') && $request->file('image')->isValid()) {
            $file = $request->file('image');
        }

        // 1. Pengecekan File Gambar:
        // HANYA jalankan proses upload jika ada file gambar yang benar-benar diunggah dari request
        if ($file) {
            try {
                if (env('CLOUDINARY_URL') || config('cloudinary.cloud_url')) {
                    $uploaded = Cloudinary::upload($file->getRealPath(), [
                        'folder' => 'profile'
                    ]);
                    $uploadedUrl = $uploaded ? $uploaded->getSecurePath() : null;
                    if ($uploadedUrl) {
                        $validated['profile_image_url'] = $uploadedUrl;
                    } else {
                        throw new \Exception("Gagal mendapatkan URL dari Cloudinary");
                    }
                } else {
                    $path = $file->store('profiles', 'public');
                    $validated['profile_image_url'] = url('storage/' . $path);
                }
            } catch (\Throwable $e) {
                // Fallback jika Cloudinary error atau tidak terkonfigurasi: simpan ke storage public lokal
                $path = $file->store('profiles', 'public');
                $validated['profile_image_url'] = url('storage/' . $path);
            }
            unset($validated['profile_image'], $validated['image']);
        } else {
            // 2. Pertahankan Gambar Lama:
            // Jika request tidak memiliki file gambar baru, abaikan upload dan pertahankan profile_image_url yang sudah ada
            unset($validated['profile_image'], $validated['image']);

            if ($request->filled('profile_image_url')) {
                $validated['profile_image_url'] = $request->input('profile_image_url');
            } elseif ($request->filled('image_url')) {
                $validated['profile_image_url'] = $request->input('image_url');
            } elseif ($firstAbout && $firstAbout->profile_image_url) {
                $validated['profile_image_url'] = $firstAbout->profile_image_url;
            } else {
                unset($validated['profile_image_url']);
            }
        }

        $updateData = [
            'bio' => $validated['bio'] ?? ($firstAbout->bio ?? ''),
            'projects_completed' => $validated['projects_completed'] ?? ($firstAbout->projects_completed ?? '0'),
            'contact_email' => $validated['contact_email'] ?? ($firstAbout->contact_email ?? ''),
            'profile_image_url' => $validated['profile_image_url'] ?? ($firstAbout->profile_image_url ?? null),
        ];

        if (array_key_exists('gpa', $validated)) {
            $updateData['gpa'] = $validated['gpa'];
        } elseif ($firstAbout && isset($firstAbout->gpa)) {
            $updateData['gpa'] = $firstAbout->gpa;
        }

        // Gunakan updateOrCreate untuk menyimpan data dengan aman
        $about = About::updateOrCreate(
            ['id' => $firstAbout ? $firstAbout->id : 1],
            $updateData
        );

        $about->refresh();

        return response()->json([
            'message' => 'Data About berhasil disimpan',
            'data' => $this->formatAboutResponse($about)
        ], 200);
    }
}
