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
     */
    public function index()
    {
        $about = About::first();

        return response()->json([
            'message' => 'Data about berhasil diambil',
            'data' => $about
        ], 200);
    }

    /**
     * Perbarui atau Simpan Data About (Baris Pertama)
     *
     * @authenticated
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'bio' => 'required|string',
            'years_experience' => 'required|string|max:50',
            'projects_completed' => 'required|string|max:50',
            'contact_email' => 'required|string|email|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'profile_image_url' => 'nullable|string|max:1000',
        ]);

        // Upload foto profil ke Cloudinary jika ada file baru
        if ($request->hasFile('profile_image')) {
            $uploadedUrl = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                'folder' => 'profile'
            ])->getSecurePath();
            $validated['profile_image_url'] = $uploadedUrl;
            unset($validated['profile_image']);
        } elseif ($request->hasFile('image')) {
            $uploadedUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'profile'
            ])->getSecurePath();
            $validated['profile_image_url'] = $uploadedUrl;
            unset($validated['image']);
        } elseif ($request->hasFile('profile_image_url')) {
            $uploadedUrl = Cloudinary::upload($request->file('profile_image_url')->getRealPath(), [
                'folder' => 'profile'
            ])->getSecurePath();
            $validated['profile_image_url'] = $uploadedUrl;
        }

        $about = About::first();

        if ($about) {
            $about->update($validated);
        } else {
            $about = About::create($validated);
        }

        return response()->json([
            'message' => 'Data About berhasil diperbarui',
            'data' => $about
        ], 200);
    }
}
