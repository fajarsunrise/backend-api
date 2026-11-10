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
            ->get();

        return response()->json([
            'message' => 'Daftar projek berhasil diambil',
            'data' => $projects
        ], 200);
    }

    /**
     * Buat Projek Baru
     *
     * Menyimpan data projek baru ke dalam database.
     * Mendukung upload file gambar langsung ke Cloudinary atau URL gambar eksternal.
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

        // 1. Cek jika ada file gambar yang diunggah -> upload ke Cloudinary
        if ($request->hasFile('image')) {
            $uploadedUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'projects'
            ])->getSecurePath();
            $validated['image_url'] = $uploadedUrl;
            unset($validated['image']);
        } elseif ($request->hasFile('image_url')) {
            $uploadedUrl = Cloudinary::upload($request->file('image_url')->getRealPath(), [
                'folder' => 'projects'
            ])->getSecurePath();
            $validated['image_url'] = $uploadedUrl;
        }

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

        // Cek jika ada unggahan gambar baru
        if ($request->hasFile('image')) {
            $uploadedUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'projects'
            ])->getSecurePath();
            $validated['image_url'] = $uploadedUrl;
            unset($validated['image']);
        } elseif ($request->hasFile('image_url')) {
            $uploadedUrl = Cloudinary::upload($request->file('image_url')->getRealPath(), [
                'folder' => 'projects'
            ])->getSecurePath();
            $validated['image_url'] = $uploadedUrl;
        }

        // Normalisasi format technologies
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

