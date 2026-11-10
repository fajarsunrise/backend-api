<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\Request;

/**
 * @group Manajemen Pengalaman Kerja (Experience)
 *
 * Endpoint API untuk mengelola data riwayat pengalaman kerja (CRUD).
 */
class ExperienceController extends Controller
{
    /**
     * Dapatkan Seluruh Riwayat Pengalaman
     */
    public function index()
    {
        $experiences = Experience::latest()->get();

        return response()->json([
            'message' => 'Daftar pengalaman kerja berhasil diambil',
            'data' => $experiences
        ], 200);
    }

    /**
     * Tambah Pengalaman Baru
     *
     * @authenticated
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'start_date' => 'required|string|max:100',
            'end_date' => 'required|string|max:100',
            'description' => 'required|string',
        ]);

        $experience = Experience::create($validated);

        return response()->json([
            'message' => 'Pengalaman kerja berhasil ditambahkan',
            'data' => $experience
        ], 201);
    }

    /**
     * Detail Pengalaman
     */
    public function show(Experience $experience)
    {
        return response()->json([
            'message' => 'Detail pengalaman kerja berhasil diambil',
            'data' => $experience
        ], 200);
    }

    /**
     * Ubah Pengalaman
     *
     * @authenticated
     */
    public function update(Request $request, Experience $experience)
    {
        $validated = $request->validate([
            'role' => 'sometimes|required|string|max:255',
            'company' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required|string|max:100',
            'end_date' => 'sometimes|required|string|max:100',
            'description' => 'sometimes|required|string',
        ]);

        $experience->update($validated);

        return response()->json([
            'message' => 'Pengalaman kerja berhasil diperbarui',
            'data' => $experience
        ], 200);
    }

    /**
     * Hapus Pengalaman
     *
     * @authenticated
     */
    public function destroy(Experience $experience)
    {
        $experience->delete();

        return response()->json([
            'message' => 'Pengalaman kerja berhasil dihapus'
        ], 200);
    }
}

