<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

/**
 * @group Manajemen Sertifikat
 *
 * Endpoint API untuk mengelola data sertifikat kompetensi (CRUD).
 */
class CertificateController extends Controller
{
    /**
     * Dapatkan Daftar Sertifikat
     *
     * Mengambil seluruh daftar sertifikat (bisa difilter dengan kata kunci pencarian).
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $certificates = Certificate::when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%')
                             ->orWhere('issued_by', 'like', '%' . $search . '%');
            })
            ->latest('issue_date')
            ->get();

        return response()->json([
            'message' => 'Daftar sertifikat berhasil diambil',
            'data' => $certificates
        ], 200);
    }

    /**
     * Tambah Sertifikat Baru
     *
     * Menyimpan data sertifikat baru ke database.
     * File gambar akan otomatis diunggah ke folder 'certificates' di Cloudinary.
     *
     * @authenticated
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'issued_by' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url' => 'nullable|string|max:1000',
        ]);

        // Cek jika ada file gambar yang diunggah -> upload ke Cloudinary
        if ($request->hasFile('image')) {
            $uploadedUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'certificates'
            ])->getSecurePath();
            $validated['image_url'] = $uploadedUrl;
            unset($validated['image']);
        } elseif ($request->hasFile('image_url')) {
            $uploadedUrl = Cloudinary::upload($request->file('image_url')->getRealPath(), [
                'folder' => 'certificates'
            ])->getSecurePath();
            $validated['image_url'] = $uploadedUrl;
        }

        $certificate = Certificate::create($validated);

        return response()->json([
            'message' => 'Sertifikat berhasil dibuat',
            'data' => $certificate
        ], 201);
    }

    /**
     * Detail Sertifikat
     *
     * Menampilkan detail satu sertifikat berdasarkan ID.
     */
    public function show(Certificate $certificate)
    {
        return response()->json([
            'message' => 'Detail sertifikat berhasil diambil',
            'data' => $certificate
        ], 200);
    }

    /**
     * Ubah Sertifikat
     *
     * Memperbarui data sertifikat yang sudah ada.
     *
     * @authenticated
     */
    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'issued_by' => 'sometimes|required|string|max:255',
            'issue_date' => 'sometimes|required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url' => 'nullable|string|max:1000',
        ]);

        // Cek jika ada file gambar baru yang diunggah
        if ($request->hasFile('image')) {
            $uploadedUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'certificates'
            ])->getSecurePath();
            $validated['image_url'] = $uploadedUrl;
            unset($validated['image']);
        } elseif ($request->hasFile('image_url')) {
            $uploadedUrl = Cloudinary::upload($request->file('image_url')->getRealPath(), [
                'folder' => 'certificates'
            ])->getSecurePath();
            $validated['image_url'] = $uploadedUrl;
        }

        $certificate->update($validated);

        return response()->json([
            'message' => 'Sertifikat berhasil diperbarui',
            'data' => $certificate
        ], 200);
    }

    /**
     * Hapus Sertifikat
     *
     * Menghapus sertifikat dari database.
     *
     * @authenticated
     */
    public function destroy(Certificate $certificate)
    {
        $certificate->delete();

        return response()->json([
            'message' => 'Sertifikat berhasil dihapus'
        ], 200);
    }
}

