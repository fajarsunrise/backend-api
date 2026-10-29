<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleService;
use App\Http\Resources\ArticleResource;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use Illuminate\Http\Request;


/**
 * @group Manajemen Artikel
 *
 * Endpoint untuk mengelola data artikel (CRUD).
 */

class ArticleController extends Controller
{
    protected $articleService;

    // Menyuntikkan (inject) ArticleService ke dalam Controller ini
    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * Dapatkan Semua Artikel
     *
     * Menampilkan daftar seluruh artikel yang ada di sistem.
     *
     * @authenticated
     */

    public function index()
    {
        $articles = $this->articleService->getAllArticles();
// 2. Gunakan 'collection' karena data yang dikembalikan berjumlah banyak (array)
        return ArticleResource::collection($articles);
    }

/**
     * Buat Artikel Baru
     *
     * Menambahkan data artikel baru ke dalam database.
     *
     * @authenticated
     * @bodyParam judul string required Judul dari artikel. Example: Belajar API Laravel
     * @bodyParam konten string required Isi lengkap dari artikel. Example: Ini adalah contoh konten artikel.
     */

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

/**
     * Detail Artikel
     *
     * Mengambil satu data artikel spesifik berdasarkan ID.
     *
     * @authenticated
     * @urlParam article integer required ID dari artikel. Example: 1
     */

    public function show(Article $article)
    {
// 3. Gunakan 'new' karena data yang dikembalikan hanya satu baris
        return new ArticleResource($article);
    }

/**
     * Ubah Artikel
     *
     * Memperbarui data artikel yang sudah ada.
     *
     * @authenticated
     * @urlParam article integer required ID artikel. Example: 1
     * @bodyParam judul string Judul baru artikel. Example: Judul Direvisi
     * @bodyParam konten string Konten baru artikel.
     */

// 4. Ganti 'Request $request' menjadi 'UpdateArticleRequest $request'
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $updatedArticle = $this->articleService->updateArticle($article, $request->validated());

        return response()->json([
            'message' => 'Artikel diperbarui',
            'data' => new ArticleResource($updatedArticle)
        ]);
    }

/**
     * Hapus Artikel
     *
     * Menghapus artikel dari database secara permanen.
     *
     * @authenticated
     * @urlParam article integer required ID artikel yang akan dihapus. Example: 1
     */

    public function destroy(Article $article)
    {
        $this->articleService->deleteArticle($article);
        return response()->json(['message' => 'Artikel dihapus']);
    }
}
