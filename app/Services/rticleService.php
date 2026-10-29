<?php

namespace App\Services;

use App\Models\Article;

class ArticleService
{
    public function getAllArticles()
    {
        return Article::all();
    }

    public function createArticle(array $data)
    {
        return Article::create($data);
    }

    public function updateArticle(Article $article, array $data)
    {
        $article->update($data);
        return $article;
    }

    public function deleteArticle(Article $article)
    {
        $article->delete();
        return true;
    }
}
