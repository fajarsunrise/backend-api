<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    // 1. Tampilkan semua data (Read)
    public function index()
    {
        return response()->json(Article::all());
    }

    // 2. Simpan data baru (Create)
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string'
        ]);

        $article = Article::create($request->all());
        return response()->json(['message' => 'Artikel berhasil dibuat', 'data' => $article], 201);
    }

    // 3. Tampilkan satu data spesifik (Read)
    public function show(Article $article)
    {
        return response()->json($article);
    }

    // 4. Perbarui data (Update)
    public function update(Request $request, Article $article)
    {
        $request->validate([
            'judul' => 'sometimes|required|string|max:255',
            'konten' => 'sometimes|required|string'
        ]);

        $article->update($request->all());
        return response()->json(['message' => 'Artikel diperbarui', 'data' => $article]);
    }

    // 5. Hapus data (Delete)
    public function destroy(Article $article)
    {
        $article->delete();
        return response()->json(['message' => 'Artikel dihapus']);
    }
}
