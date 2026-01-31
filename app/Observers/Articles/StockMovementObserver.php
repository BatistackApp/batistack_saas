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
        // On ne vérifie les alertes que pour les sorties ou les ajustements négatifs
        if (in_array($movement->type, [StockMovementType::Exit, StockMovementType::Adjustment])) {
            // On délègue la vérification à un Job asynchrone pour ne pas ralentir l'UI
            CheckArticleAlertLevelJob::dispatch($movement->article);
        }
    }
}
