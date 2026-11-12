<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

/**
 * @group Manajemen Projek
 *
 * Endpoint API untuk mengelola data projek portofolio (CRUD).
 */
class ProjectController extends Controller
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
     * Dapatkan Daftar Projek
     *
     * Mengambil semua daftar projek portofolio (bisa difilter dengan kata kunci pencarian).
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $projects = Project::when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%')
                             ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->latest()
            ->get()
            ->map(function ($project) {
                $project->image_url = $this->formatImageUrl($project->image_url);
                return $project;
            });

        return response()->json([
            'message' => 'Daftar projek berhasil diambil',
            'data' => $projects
        ], 200);
    }

    /**
     * Buat Projek Baru
     *
     * Menyimpan data projek baru ke dalam database.
     * Mendukung upload file gambar langsung ke Cloudinary atau fallback lokal.
     *
     * @authenticated
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url' => 'nullable|string|max:1000',
            'technologies' => 'nullable',
            'demo_link' => 'nullable|string|max:500',
        ]);

        $imageUrl = null;

        // 1. Pengecekan File Gambar:
        // HANYA jalankan proses upload jika ada file gambar fisik yang benar-benar diunggah dan valid
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imageFile = $request->file('image');
            try {
                // Cek ketersediaan konfigurasi Cloudinary
                if (env('CLOUDINARY_URL') || config('cloudinary.cloud_url')) {
                    $uploaded = Cloudinary::upload($imageFile->getRealPath(), [
                        'folder' => 'projects'
                    ]);
                    $uploadedUrl = $uploaded ? $uploaded->getSecurePath() : null;
                    if ($uploadedUrl) {
                        $imageUrl = $uploadedUrl;
                    } else {
                        throw new \Exception("Gagal memperoleh URL dari Cloudinary");
                    }
                } else {
                    // Simpan ke storage lokal jika Cloudinary tidak dikonfigurasi
                    $path = $imageFile->store('projects', 'public');
                    $imageUrl = url('storage/' . $path);
                }
            } catch (\Throwable $e) {
                // Fallback aman ke penyimpanan lokal jika Cloudinary error untuk cegah error 500
                try {
                    $path = $imageFile->store('projects', 'public');
                    $imageUrl = url('storage/' . $path);
                } catch (\Throwable $storageErr) {
                    $imageUrl = null;
                }
            }
            unset($validated['image']);
        } elseif ($request->filled('image_url')) {
            $imageUrl = $request->input('image_url');
        }

        $validated['image_url'] = $imageUrl;
        unset($validated['image']);

        // 2. Normalisasi format technologies (bisa array, string JSON, atau koma)
        if (isset($validated['technologies'])) {
            if (is_string($validated['technologies'])) {
                $decoded = json_decode($validated['technologies'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $validated['technologies'] = $decoded;
                } else {
                    $validated['technologies'] = array_values(array_filter(array_map('trim', explode(',', $validated['technologies']))));
                }
            }
        }

        $project = Project::create($validated);
        $project->image_url = $this->formatImageUrl($project->image_url);

        return response()->json([
            'message' => 'Projek berhasil dibuat',
            'data' => $project
        ], 201);
    }

    /**
     * Detail Projek
     *
     * Menampilkan satu data projek berdasarkan ID.
     */
    public function show(Project $project)
    {
        $project->image_url = $this->formatImageUrl($project->image_url);

        return response()->json([
            'message' => 'Detail projek berhasil diambil',
            'data' => $project
        ], 200);
    }

    /**
     * Ubah Projek
     *
     * Memperbarui data projek yang sudah ada.
     *
     * @authenticated
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url' => 'nullable|string|max:1000',
            'technologies' => 'nullable',
            'demo_link' => 'nullable|string|max:500',
        ]);

        // 1. Pengecekan File Gambar:
        // HANYA jalankan proses upload jika ada file gambar baru yang benar-benar diunggah dan valid
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imageFile = $request->file('image');
            try {
                if (env('CLOUDINARY_URL') || config('cloudinary.cloud_url')) {
                    $uploaded = Cloudinary::upload($imageFile->getRealPath(), [
                        'folder' => 'projects'
                    ]);
                    $uploadedUrl = $uploaded ? $uploaded->getSecurePath() : null;
                    if ($uploadedUrl) {
                        $validated['image_url'] = $uploadedUrl;
                    } else {
                        throw new \Exception("Gagal memperoleh URL dari Cloudinary");
                    }
                } else {
                    $path = $imageFile->store('projects', 'public');
                    $validated['image_url'] = url('storage/' . $path);
                }
            } catch (\Throwable $e) {
                // Fallback aman ke penyimpanan lokal jika Cloudinary gagal untuk cegah error 500
                try {
                    $path = $imageFile->store('projects', 'public');
                    $validated['image_url'] = url('storage/' . $path);
                } catch (\Throwable $storageErr) {
                    // Jika penyimpanan lokal gagal, pertahankan gambar lama jika ada, atau null
                    $validated['image_url'] = $project->image_url ?? null;
                }
            }
            unset($validated['image']);
        } else {
            // 2. Pertahankan Gambar Lama:
            // Jika request tidak memiliki file gambar baru, abaikan proses upload dan jangan ubah/timpa image_url yang sudah ada
            unset($validated['image']);

            if ($request->filled('image_url')) {
                $validated['image_url'] = $request->input('image_url');
            } elseif ($project->image_url) {
                $validated['image_url'] = $project->image_url;
            } else {
                unset($validated['image_url']);
            }
        }

        // 3. Normalisasi format technologies
        if (isset($validated['technologies'])) {
            if (is_string($validated['technologies'])) {
                $decoded = json_decode($validated['technologies'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $validated['technologies'] = $decoded;
                } else {
                    $validated['technologies'] = array_values(array_filter(array_map('trim', explode(',', $validated['technologies']))));
                }
            }
        }

        $project->update($validated);
        $project->refresh();
        $project->image_url = $this->formatImageUrl($project->image_url);

        return response()->json([
            'message' => 'Projek berhasil diperbarui',
            'data' => $project
        ], 200);
    }

    /**
     * Hapus Projek
     *
     * Menghapus data projek dari database.
     *
     * @authenticated
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'message' => 'Projek berhasil dihapus'
        ], 200);
    }
}
