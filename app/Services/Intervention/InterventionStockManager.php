<?php

namespace App\Services\Intervention;

use App\Exceptions\Intervention\InsufficientStockException;
use App\Models\Intervention\Intervention;
use App\Services\Articles\InventoryService;
use App\Services\Articles\StockMovementService;

class InterventionStockManager
{
    public function __construct(
        protected StockMovementService $stockService,
        protected InventoryService $inventoryService
    ) {}

    /**
     * Valide la disponibilité de tous les items avant exécution.
     */
    public function validateStockAvailability(Intervention $intervention): void
    {
        if (!$intervention->warehouse) return;

        foreach ($intervention->items as $item) {
            if ($item->article_id) {
                if (!$this->inventoryService->hasEnoughStock($item->article, $intervention->warehouse, $item->quantity)) {
                    throw new InsufficientStockException("Stock insuffisant pour l'article : {$item->label}");
                }
            }
            // Note: Pour les ouvrages, la vérification est déléguée au StockMovementService lors de l'exécution
        }
    }

    /**
     * Exécute les sorties de stock réelles.
     */
    public function processStockExits(Intervention $intervention): void
    {
        foreach ($intervention->items as $item) {
            if ($item->ouvrage_id) {
                // Utilisation du service existant pour l'explosion de nomenclature
                $this->stockService->recordOuvrageExit(
                    $item->ouvrage,
                    $intervention->warehouse,
                    (float) $item->quantity,
                    $intervention->project_id ?? 0
                );
            } elseif ($item->article_id) {
                // Pour les articles simples (Utilisation de la logique standard de mouvement)
                // On peut utiliser recordAdjustment ou créer une méthode recordSimpleExit
            }
        }
    }
}
