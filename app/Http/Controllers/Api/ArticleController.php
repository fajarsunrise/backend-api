<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleService;
use App\Http\Resources\ArticleResource;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
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
// 2. Gunakan 'collection' karena data yang dikembalikan berjumlah banyak (array)
        return ArticleResource::collection($articles);
    }

// 3. Ganti 'Request $request' menjadi 'StoreArticleRequest $request'
    public function store(StoreArticleRequest $request)
    {
        // $request->validated() otomatis mengambil data yang sudah lolos seleksi
        $article = $this->articleService->createArticle($request->validated());

        return response()->json([
            'message' => 'Artikel berhasil dibuat',
            'data' => new ArticleResource($article)
        ], 201);
    }

    public function show(Article $article)
    {
// 3. Gunakan 'new' karena data yang dikembalikan hanya satu baris
        return new ArticleResource($article);
    }

// 4. Ganti 'Request $request' menjadi 'UpdateArticleRequest $request'
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $updatedArticle = $this->articleService->updateArticle($article, $request->validated());

        return response()->json([
            'message' => 'Artikel diperbarui',
            'data' => new ArticleResource($updatedArticle)
        ]);
    }

    public function destroy(Article $article)
    {
        $this->articleService->deleteArticle($article);
        return response()->json(['message' => 'Artikel dihapus']);
    }
}
