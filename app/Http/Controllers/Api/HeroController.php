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
     * Helper untuk memformat URL gambar agar selalu lengkap dan valid
     */
    private function formatImageUrl(?string $imageUrl): string
    {
        if (empty($imageUrl)) {
            return '';
        }

        // Jika sudah berupa URL absolut (Cloudinary / http / https / data:), kembalikan langsung
        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://') || str_starts_with($imageUrl, 'data:')) {
            return $imageUrl;
        }

        // Jika berupa relative path penyimpanan lokal, ubah menjadi URL absolut menggunakan url()
        $cleanPath = ltrim(str_replace('storage/', '', $imageUrl), '/');
        return url('storage/' . $cleanPath);
    }

    /**
     * Helper untuk memformat data respons Hero secara eksplisit dengan kunci image_url
     */
    private function formatHeroResponse(?Hero $hero): array
    {
        if (!$hero) {
            return [
                'id' => null,
                'greeting' => '',
                'description' => '',
                'marquee_text' => '',
                'image_url' => '',
                'created_at' => null,
                'updated_at' => null,
            ];
        }

        return [
            'id' => $hero->id,
            'greeting' => $hero->greeting ?? '',
            'description' => $hero->description ?? '',
            'marquee_text' => $hero->marquee_text ?? '',
            'image_url' => $this->formatImageUrl($hero->image_url),
            'created_at' => $hero->created_at,
            'updated_at' => $hero->updated_at,
        ];
    }

    /**
     * Dapatkan Data Hero (Baris Pertama)
     */
    public function index()
    {
        try {
            $hero = Hero::first();

            return response()->json([
                'message' => 'Data hero berhasil diambil',
                'data' => $this->formatHeroResponse($hero)
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Data hero belum tersedia',
                'data' => $this->formatHeroResponse(null)
            ], 200);
        }
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

        $hero = Hero::first();

        // 1. Pengecekan File Gambar:
        // HANYA jalankan proses upload jika ada file gambar fisik yang diunggah
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imageFile = $request->file('image');
            try {
                // Cek ketersediaan konfigurasi Cloudinary
                if (env('CLOUDINARY_URL') || config('cloudinary.cloud_url')) {
                    $uploaded = Cloudinary::upload($imageFile->getRealPath(), [
                        'folder' => 'hero'
                    ]);
                    $uploadedUrl = $uploaded ? $uploaded->getSecurePath() : null;
                    if ($uploadedUrl) {
                        $validated['image_url'] = $uploadedUrl;
                    } else {
                        throw new \Exception("Gagal memperoleh secure URL dari Cloudinary");
                    }
                } else {
                    // Simpan ke storage lokal jika Cloudinary tidak dikonfigurasi
                    $path = $imageFile->store('heroes', 'public');
                    $validated['image_url'] = url('storage/' . $path);
                }
            } catch (\Throwable $e) {
                // Fallback aman ke penyimpanan lokal jika Cloudinary error
                $path = $imageFile->store('heroes', 'public');
                $validated['image_url'] = url('storage/' . $path);
            }
            unset($validated['image']);
        } else {
            // 2. Pertahankan Gambar Lama:
            // Jika request tidak memiliki file gambar baru, abaikan proses upload dan jangan ubah/timpa image_url yang sudah ada
            unset($validated['image']);

            if ($request->filled('image_url')) {
                $validated['image_url'] = $request->input('image_url');
            } elseif ($hero && $hero->image_url) {
                $validated['image_url'] = $hero->image_url;
            } else {
                unset($validated['image_url']);
            }
        }

        // 3. Simpan atau perbarui data baris pertama ke database
        if ($hero) {
            $hero->update($validated);
            $hero->refresh();
        } else {
            $hero = Hero::create($validated);
        }

        // 4. Kembalikan respons JSON dengan image_url secara eksplisit
        return response()->json([
            'message' => 'Konten Hero berhasil diperbarui',
            'data' => $this->formatHeroResponse($hero)
        ], 200);
    }
}
