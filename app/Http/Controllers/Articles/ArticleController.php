<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\ArticleRequest;
use App\Models\Articles\Article;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    public function index(): JsonResponse
    {
        $articles = Article::with(['category', 'warehouses'])
            ->latest()
            ->get()
            ->map(function ($article) {
                return [
                    'id' => $article->id,
                    'sku' => $article->sku,
                    'name' => $article->name,
                    'unit' => $article->unit,
                    'cump_ht' => $article->cump_ht,
                    'total_stock' => $article->total_stock,
                    'alert_level' => $article->alert_stock,
                    'is_low_stock' => $article->total_stock <= $article->alert_stock,
                ];
            });

        return response()->json($articles);
    }

    public function store(ArticleRequest $request): JsonResponse
    {
        $article = Article::create($request->validated());
        return response()->json($article, 201);
    }

    public function show(Article $article): JsonResponse
    {
        return response()->json($article->load(['category', 'warehouses', 'supplier']));
    }

    public function update(ArticleRequest $request, Article $article): JsonResponse
    {
        $article->update($request->validated());
        return response()->json($article);
    }

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();
        return response()->json(null, 204);
    }
}
