<?php

namespace App\Services\Articles;

use App\Models\Articles\Ouvrage;
use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;

class CalculationService
{
    public function calculateArticleValue(Article $article, Warehouse $warehouse): float
    {
        $stock = $warehouse->stocks()
            ->where('article_id', $article->id)
            ->first();

        if (!$stock) {
            return 0;
        }

        return $stock->quantity * $article->purchase_price;
    }

    public function calculateWarehouseValue(Warehouse $warehouse): float
    {
        return $warehouse->stocks()
            ->with('article')
            ->get()
            ->sum(fn ($stock) => $stock->quantity * $stock->article->purchase_price);
    }

    public function calculateOuvrageCost(Ouvrage $ouvrage): float
    {
        return $ouvrage->items()
            ->with('article')
            ->get()
            ->sum(fn ($item) => $item->quantity * $item->article->purchase_price);
    }

    public function calculateOuvrageSellingPrice(Ouvrage $ouvrage, float $marginPercentage): float
    {
        $cost = $this->calculateOuvrageCost($ouvrage);
        return $cost * (1 + ($marginPercentage / 100));
    }
}
