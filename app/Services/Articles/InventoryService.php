<?php

namespace App\Services\Articles;
use App\Jobs\Articles\RecalculateOuvrageCostsJob;
use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;

/**
 * Service gérant la valorisation et les analyses de stocks.
 */
class InventoryService
{
    /**
     * Recalcule le Coût Unitaire Moyen Pondéré (CUMP) après une réception.
     * Formule : (Valeur Stock Actuel + Valeur Réception) / (Quantité Totale)
     */
    public function updateCump(Article $article, float $receivedQty, float $purchasePriceHt): void
    {
        $currentQty = $article->total_stock;
        $currentCump = (float) $article->cump_ht;

        $totalValue = ($currentQty * $currentCump) + ($receivedQty * $purchasePriceHt);
        $totalQty = $currentQty + $receivedQty;

        if ($totalQty > 0) {
            $newCump = $totalValue / $totalQty;
            $article->update([
                'cump_ht' => round($newCump, 2),
                'purchase_price_ht' => $purchasePriceHt // Mise à jour du dernier prix d'achat
            ]);
            // DÉCLENCHEUR : Si le prix a changé, on lance le recalcul des ouvrages impactés
            if ($currentCump != $newCump) {
                RecalculateOuvrageCostsJob::dispatch($article, $currentCump);
            }
        }
    }

    /**
     * Vérifie si un article est disponible en quantité suffisante dans un dépôt.
     */
    public function hasEnoughStock(Article $article, Warehouse $warehouse, float $requestedQty): bool
    {
        $stock = $article->warehouses()
            ->where('warehouse_id', $warehouse->id)
            ->first()?->pivot?->quantity ?? 0;

        return $stock >= $requestedQty;
    }
}
