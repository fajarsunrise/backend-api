<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ArticleService
{
    public function getAllArticles($search = null, $categoryId = null)
    {
        return Article::with(['category', 'tags'])
            ->when($search, function ($query, $search) {
                return $query->where('judul', 'like', '%' . $search . '%')
                             ->orWhere('konten', 'like', '%' . $search . '%');
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->paginate(10);
    }

    public function createArticle(array $data)
    {
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        // 1. Cek apakah ada file yang diunggah
        if (isset($data['gambar_sampul'])) {
            // Upload gambar ke Cloudinary di dalam folder 'articles'
            $uploadedFileUrl = Cloudinary::upload($data['gambar_sampul']->getRealPath(), [
                'folder' => 'articles'
            ])->getSecurePath();

            // Simpan URL aman (https) Cloudinary ke database
            $data['gambar_sampul'] = $uploadedFileUrl;
        }

        $article = Article::create($data);

        if (!empty($tags)) {
            $article->tags()->attach($tags);
        }

        return $article;
    }

    public function updateArticle(Article $article, array $data)
    {
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        // 1. Jika ada file gambar baru yang diunggah
        if (isset($data['gambar_sampul'])) {
            // Catatan: Untuk skala portofolio, kita cukup menimpa URL di database.
            // Penghapusan fisik di server Cloudinary membutuhkan ekstraksi public_id dari URL,
            // yang bisa kita abaikan karena kapasitas gratis Cloudinary sangat besar.

            // Upload gambar baru ke Cloudinary
            $uploadedFileUrl = Cloudinary::upload($data['gambar_sampul']->getRealPath(), [
                'folder' => 'articles'
            ])->getSecurePath();

            $data['gambar_sampul'] = $uploadedFileUrl;
        }

        $article->update($data);

        if (isset($data['tags'])) {
            $article->tags()->sync($tags);
        }

        return $article;
    }

    public function deleteArticle(Article $article)
    {
        // Hapus data dari database (URL akan ikut terhapus dari tabel)
        $article->delete();

        return true;
    }
}
