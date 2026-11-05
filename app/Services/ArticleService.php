<?php

namespace App\Services;
use Illuminate\Support\Facades\Storage;
use App\Models\Article;

class ArticleService
{
public function getAllArticles($search = null, $categoryId = null)
{
    return Article::with(['category', 'tags'])
        // Jika ada pencarian, cari berdasarkan judul atau konten
        ->when($search, function ($query, $search) {
            return $query->where('judul', 'like', '%' . $search . '%')
                         ->orWhere('konten', 'like', '%' . $search . '%');
        })
        // Jika ada filter kategori, cari yang ID kategorinya cocok
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
            // Simpan file ke folder storage/app/public/articles
            // Simpan path-nya untuk dimasukkan ke database
            $data['gambar_sampul'] = $data['gambar_sampul']->store('articles', 'public');
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
            // Hapus gambar lama secara fisik dari server agar hardisk tidak penuh
            if ($article->gambar_sampul) {
                Storage::disk('public')->delete($article->gambar_sampul);
            }

            // Simpan gambar baru
            $data['gambar_sampul'] = $data['gambar_sampul']->store('articles', 'public');
        }

        $article->update($data);

        if (isset($data['tags'])) {
            $article->tags()->sync($tags);
        }

        return $article;
    }

public function deleteArticle(Article $article)
{
    // 1. Cek apakah artikel ini punya gambar sampul
    if ($article->gambar_sampul) {
        // 2. Hapus file fisik dari folder storage/app/public
        Storage::disk('public')->delete($article->gambar_sampul);
    }

    // 3. Hapus data dari database
    $article->delete();

    return true;
}
}
