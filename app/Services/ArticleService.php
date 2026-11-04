<?php

namespace App\Services;

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
    // Ambil data tags, lalu hapus dari array utama agar tidak error saat create artikel
    $tags = $data['tags'] ?? [];
    unset($data['tags']);

    $article = Article::create($data);

    // Tautkan ID tag ke artikel melalui tabel pivot menggunakan attach()
    if (!empty($tags)) {
        $article->tags()->attach($tags);
    }

    return $article;
}

public function updateArticle(Article $article, array $data)
{
    $tags = $data['tags'] ?? [];
    unset($data['tags']);

    $article->update($data);

    // sync() sangat pintar: ia akan menghapus tag lama dan menggantinya dengan tag baru
    if (isset($data['tags'])) {
        $article->tags()->sync($tags);
    }

    return $article;
}

    public function deleteArticle(Article $article)
    {
        $article->delete();
        return true;
    }
}
