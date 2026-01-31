<?php

namespace App\Observers\Articles;

use App\Enums\Articles\StockMovementType;
use App\Jobs\Articles\CheckArticleAlertLevelJob;
use App\Models\Articles\StockMovement;

class StockMovementObserver
{
    /**
     * Après chaque mouvement, on vérifie si le niveau de stock nécessite une action.
     */
    public function created(StockMovement $movement): void
    {
        $article = $movement->article;
        $quantity = (float) $movement->quantity;

        /**
         * Logique simplifiée :
         * - Entry, Return, Adjustment : On ajoute la quantité (l'Adjustment est signé en base).
         * - Exit : On soustrait la quantité.
         * - Transfer : 0 (pas d'impact sur le stock global de l'article).
         */
        $delta = match ($movement->type) {
            StockMovementType::Entry,
            StockMovementType::Return,
            StockMovementType::Adjustment => $quantity,
            StockMovementType::Exit => -$quantity,
            default => 0,
        };

        if ($delta !== 0.0) {
            $article->increment('total_stock', $delta);
        }

        // Déclenchement des alertes (en cas de baisse de stock ou d'ajustement)
        if ($delta < 0 || $movement->type === StockMovementType::Adjustment) {
            CheckArticleAlertLevelJob::dispatch($article);
        }
    }
}
