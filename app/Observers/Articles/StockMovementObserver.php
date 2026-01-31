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
            $article = $movement->article;
            $quantity = (float) $movement->quantity;

            // Détermination du delta à appliquer au stock total de l'article
            $delta = match ($movement->type) {
                StockMovementType::Entry, StockMovementType::Return => $quantity,
                StockMovementType::Exit => -$quantity,
                StockMovementType::Adjustment => $this->getAdjustmentDelta($movement),
                StockMovementType::Transfer => 0, // Un transfert ne change pas le stock total de l'article
                default => 0,
            };

            if ($delta !== 0.0) {
                $article->increment('total_stock', $delta);
            }

            // Déclenchement des alertes si nécessaire
            if ($delta < 0 || $movement->type === StockMovementType::Adjustment) {
                CheckArticleAlertLevelJob::dispatch($article);
            }
        }
    }

    /**
     * Calcule le delta pour un ajustement (le signe est souvent dans la note ou la logique métier).
     */
    protected function getAdjustmentDelta(StockMovement $movement): float
    {
        // On suppose ici que pour un ajustement, la quantité stockée est déjà signée
        // ou que la logique de création du mouvement a correctement défini la valeur.
        // Si la quantité est toujours positive en base, il faut se baser sur les notes [Gain]/[Perte]
        if (str_contains($movement->notes, '[Perte]')) {
            return -(float) $movement->quantity;
        }

        return (float) $movement->quantity;
    }
}
