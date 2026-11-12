<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

/**
 * @group Manajemen Pesan (Inbox)
 *
 * Endpoint API untuk menerima dan mengelola pesan masuk dari form kontak.
 */
class ContactMessageController extends Controller
{
    /**
     * Dapatkan Semua Pesan Masuk (Admin)
     *
     * Mengambil seluruh daftar pesan masuk, diurutkan dari yang terbaru.
     *
     * @authenticated
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $messages = ContactMessage::when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%")
                             ->orWhere('subject', 'like', "%{$search}%")
                             ->orWhere('message', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Daftar pesan masuk berhasil diambil',
            'data' => $messages
        ], 200);
    }

    /**
     * Simpan Pesan Baru (Publik)
     *
     * Menerima pesan yang dikirim oleh pengunjung melalui formulir kontak.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $message = ContactMessage::create($validated);

        return response()->json([
            'message' => 'Pesan Anda berhasil dikirim! Terima kasih telah menghubungi saya.',
            'data' => $message
        ], 201);
    }

    /**
     * Hapus Pesan Masuk (Admin)
     *
     * Menghapus satu pesan masuk berdasarkan ID.
     *
     * @authenticated
     */
    public function destroy($id)
    {
        $message = ContactMessage::find($id);

        if (!$message) {
            return response()->json([
                'message' => 'Pesan tidak ditemukan'
            ], 404);
        }

        $message->delete();

        return response()->json([
            'message' => 'Pesan berhasil dihapus'
        ], 200);
    }
}

