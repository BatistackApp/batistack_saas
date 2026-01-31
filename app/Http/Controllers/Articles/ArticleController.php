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
        $articles = Article::with(['category'])
            // On calcule la somme des quantités dans la table pivot directement via SQL
            ->withSum('warehouses as total_stock_calculated', 'article_warehouse.quantity')
            ->latest()
            ->paginate(20);

        // On transforme la collection paginée pour formater la réponse API
        $articles->getCollection()->transform(function ($article) {
            $totalStock = (float) ($article->total_stock_calculated ?? 0);

            return [
                'id' => $article->id,
                'sku' => $article->sku,
                'name' => $article->name,
                'unit' => $article->unit,
                'cump_ht' => $article->cump_ht,
                'total_stock' => $totalStock,
                'alert_level' => $article->alert_stock,
                'is_low_stock' => $totalStock <= (float) $article->alert_stock,
                'barcode' => $article->barcode,
                'poids' => $article->poids,
                'volume' => $article->volume,
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
