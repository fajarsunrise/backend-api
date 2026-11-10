<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

/**
 * @group Manajemen Tools & Teknologi
 *
 * Endpoint API untuk mengelola ikon dan nama tools/teknologi portofolio (CRUD).
 */
class ToolController extends Controller
{
    /**
     * Dapatkan Daftar Tools
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $tools = Tool::when($search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Daftar tools berhasil diambil',
            'data' => $tools
        ], 200);
    }

    /**
     * Tambah Tool Baru
     *
     * @authenticated
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'icon_url' => 'nullable|string|max:1000',
        ]);

        // Upload file ikon/gambar ke Cloudinary menggunakan standar yang sama dengan Project
        if ($request->hasFile('icon')) {
            $uploadedUrl = Cloudinary::upload($request->file('icon')->getRealPath(), [
                'folder' => 'tools'
            ])->getSecurePath();
            $validated['icon_url'] = $uploadedUrl;
            unset($validated['icon']);
        } elseif ($request->hasFile('image')) {
            $uploadedUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'tools'
            ])->getSecurePath();
            $validated['icon_url'] = $uploadedUrl;
            unset($validated['image']);
        } elseif ($request->hasFile('icon_url')) {
            $uploadedUrl = Cloudinary::upload($request->file('icon_url')->getRealPath(), [
                'folder' => 'tools'
            ])->getSecurePath();
            $validated['icon_url'] = $uploadedUrl;
        }

        $tool = Tool::create($validated);

        return response()->json([
            'message' => 'Tool berhasil dibuat',
            'data' => $tool
        ], 201);
    }

    /**
     * Detail Tool
     */
    public function show(Tool $tool)
    {
        return response()->json([
            'message' => 'Detail tool berhasil diambil',
            'data' => $tool
        ], 200);
    }

    /**
     * Ubah Tool
     *
     * @authenticated
     */
    public function update(Request $request, Tool $tool)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'icon_url' => 'nullable|string|max:1000',
        ]);

        // Upload file ikon/gambar baru ke Cloudinary menggunakan standar yang sama dengan Project
        if ($request->hasFile('icon')) {
            $uploadedUrl = Cloudinary::upload($request->file('icon')->getRealPath(), [
                'folder' => 'tools'
            ])->getSecurePath();
            $validated['icon_url'] = $uploadedUrl;
            unset($validated['icon']);
        } elseif ($request->hasFile('image')) {
            $uploadedUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'tools'
            ])->getSecurePath();
            $validated['icon_url'] = $uploadedUrl;
            unset($validated['image']);
        } elseif ($request->hasFile('icon_url')) {
            $uploadedUrl = Cloudinary::upload($request->file('icon_url')->getRealPath(), [
                'folder' => 'tools'
            ])->getSecurePath();
            $validated['icon_url'] = $uploadedUrl;
        }

        $tool->update($validated);

        return response()->json([
            'message' => 'Tool berhasil diperbarui',
            'data' => $tool
        ], 200);
    }

    /**
     * Hapus Tool
     *
     * @authenticated
     */
    public function destroy(Tool $tool)
    {
        $tool->delete();

        return response()->json([
            'message' => 'Tool berhasil dihapus'
        ], 200);
    }
}
