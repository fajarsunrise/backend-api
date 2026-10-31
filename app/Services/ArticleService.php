<?php

namespace App\Services;

use App\Models\Article;

class ArticleService
{
public function getAllArticles()
{
    // Tarik data Kategori dan Tag sekaligus (Eager Loading) agar performa cepat
    return Article::with(['category', 'tags'])->get();
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
