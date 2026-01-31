<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\ArticleRequest;
use App\Models\Articles\Article;

class ArticleController extends Controller
{
    public function index()
    {
        return Article::all();
    }

    public function store(ArticleRequest $request)
    {
        return Article::create($request->validated());
    }

    public function show(Article $article)
    {
        return $article;
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $article->update($request->validated());

        return $article;
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return response()->json();
    }
}
