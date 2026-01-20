<?php

namespace App\Observers\Articles;

use App\Models\Articles\Stock;
use App\Models\Articles\StockMouvement;

class StockMouvementObserver
{
    public function created(StockMouvement $mouvement): void
    {
        $stock = Stock::withTrashed()->firstOrCreate(
            [
                'tenant_id' => $mouvement->tenant_id,
                'article_id' => $mouvement->article_id,
                'warehouse_id' => $mouvement->warehouse_id,
            ],
            ['quantity' => 0]
        );

        match ($mouvement->type->value) {
            'entree', 'transfert', 'ajustement', 'production' => $stock->increment('quantity', $mouvement->quantity),
            'sortie', 'consommation' => $stock->decrement('quantity', $mouvement->quantity),
        };

        $stock->update(['last_movement_at' => now()]);
    }

    public function deleting(StockMouvement $mouvement): void
    {
        $stock = Stock::where('article_id', $mouvement->article_id)
            ->where('warehouse_id', $mouvement->warehouse_id)
            ->where('tenant_id', $mouvement->tenant_id)
            ->first();

        if (! $stock) {
            return;
        }

        match ($mouvement->type->value) {
            'entree', 'transfert', 'production', 'ajustement' => $stock->decrement('quantity', $mouvement->quantity),
            'sortie', 'consommation' => $stock->increment('quantity', $mouvement->quantity),
        };
    }
}
