<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    protected $articleService;

    // Menyuntikkan (inject) ArticleService ke dalam Controller ini
    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    public function index()
    {
        $articles = $this->articleService->getAllArticles();
        return response()->json($articles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string'
        ]);

        $article = $this->articleService->createArticle($validated);
        return response()->json(['message' => 'Artikel berhasil dibuat', 'data' => $article], 201);
    }

    public function show(Article $article)
    {
        return response()->json($article);
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'judul' => 'sometimes|required|string|max:255',
            'konten' => 'sometimes|required|string'
        ]);

        $updatedArticle = $this->articleService->updateArticle($article, $validated);
        return response()->json(['message' => 'Artikel diperbarui', 'data' => $updatedArticle]);
    }

    public function destroy(Article $article)
    {
        $this->articleService->deleteArticle($article);
        return response()->json(['message' => 'Artikel dihapus']);
    }
}
