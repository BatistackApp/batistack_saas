<?php

namespace App\Services\Articles;
use App\Jobs\Articles\RecalculateOuvrageCostsJob;
use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;
use Illuminate\Database\Eloquent\Model;

/**
 * Service gérant la valorisation et les analyses de stocks.
 */
class InventoryService
{
    /**
     * Recalcule le Coût Unitaire Moyen Pondéré (CUMP) après une réception.
     * Formule : (Valeur Stock Actuel + Valeur Réception) / (Quantité Totale)
     */
    public function updateCump(Model $item, float $receivedQty, float $purchasePriceHt): void
    {
        $currentQty = (float) ($item->total_stock ?? 0);
        $currentCump = (float) ($item->cump_ht ?? 0);

        $totalValue = ($currentQty * $currentCump) + ($receivedQty * $purchasePriceHt);
        $totalQty = $currentQty + $receivedQty;

        if ($totalQty > 0) {
            $newCump = $totalValue / $totalQty;
            $updateData = [
                'cump_ht' => round($newCump, 2),
            ];

            // Pour un article, on met aussi à jour le dernier prix d'achat
            if ($item instanceof Article) {
                $updateData['purchase_price_ht'] = $purchasePriceHt;
            }

            $item->update($updateData);

            // DÉCLENCHEUR : Si le prix a changé, on lance le recalcul des ouvrages impactés
            if ($currentCump != $newCump) {
                RecalculateOuvrageCostsJob::dispatch($item, $currentCump);
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
            ->first();

        return ($stock?->pivot?->quantity ?? 0) >= $requestedQty;
    }
}
