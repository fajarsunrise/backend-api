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
 * Dapatkan Daftar Artikel
 *
 * Mengambil semua data artikel dengan format pagination (10 data per halaman).
 *
 * @authenticated
 * @queryParam search string Kata kunci pencarian berdasarkan judul atau konten. Example: laravel
 * @queryParam category_id integer Filter berdasarkan ID Kategori tertentu. Example: 1
 */

public function index(Request $request)
{
    // Menangkap parameter ?search=... dan ?category_id=... dari URL
    $search = $request->query('search');
    $categoryId = $request->query('category_id');

    // Mengirimkan parameter tersebut ke Service
    $articles = $this->articleService->getAllArticles($search, $categoryId);

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
     * @bodyParam category_id integer ID dari kategori (opsional). Example: 1
     */

/**
 * Buat Artikel Baru
 *
 * Menyimpan artikel baru ke dalam database beserta relasi tag dan gambar sampul.
 * Pastikan request dikirim menggunakan format multipart/form-data jika menyertakan gambar.
 *
 * @authenticated
 * @bodyParam judul string required Judul artikel. Example: Belajar Laravel API
 * @bodyParam konten string required Isi konten artikel. Example: Ini adalah panduan lengkap...
 * @bodyParam category_id integer ID kategori dari artikel. Example: 1
 * @bodyParam tags int[] Array berisi ID tag. Example: [1, 2]
 * @bodyParam gambar_sampul file Gambar sampul artikel (Maks: 2MB, Format: jpeg, png, jpg, webp).
 */
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
     * @bodyParam category_id integer ID dari kategori (opsional). Example: 1
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
