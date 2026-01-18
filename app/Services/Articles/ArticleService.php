<?php

namespace App\Services\Articles;

use App\Models\Articles\Article;
use App\Models\Articles\ArticleCategory;
use Illuminate\Database\Eloquent\Collection;

class ArticleService
{
    public function create(array $data): Article
    {
        return Article::create($data);
    }

    public function update(Article $article, array $data): Article
    {
        $article->update($data);
        return $article;
    }

    public function archive(Article $article): Article
    {
        $article->update(['archived_at' => now()]);
        return $article;
    }

    public function restore(Article $article): Article
    {
        $article->update(['archived_at' => null]);
        return $article;
    }

    public function getActive(): Collection
    {
        return Article::whereNull('archived_at')->get();
    }

    public function search(string $query): Collection
    {
        return Article::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->whereNull('archived_at')
            ->get();
    }

    public function getByCategory(ArticleCategory $category): Collection
    {
        return $category->articles()->whereNull('archived_at')->get();
    }
}
