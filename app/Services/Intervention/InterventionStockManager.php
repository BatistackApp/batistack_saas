<?php

namespace App\Services\Intervention;

use App\Exceptions\Intervention\InsufficientStockException;
use App\Models\Intervention\Intervention;
use App\Services\Articles\InventoryService;
use App\Services\Articles\StockMovementService;
use Auth;

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
        if (! $intervention->warehouse) {
            return;
        }

        foreach ($intervention->items as $item) {
            if ($item->article_id) {
                if (! $this->inventoryService->hasEnoughStock($item->article, $intervention->warehouse, $item->quantity)) {
                    throw new InsufficientStockException("Stock insuffisant pour l'article : {$item->label}");
                }
            }
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
                \App\Models\Articles\StockMovement::create([
                    'tenants_id' => $intervention->tenants_id,
                    'article_id' => $item->article_id,
                    'warehouse_id' => $intervention->warehouse_id,
                    'serial_number_id' => $item->article_serial_number_id,
                    'type' => \App\Enums\Articles\StockMovementType::Exit,
                    'quantity' => $item->quantity,
                    'unit_cost_ht' => $item->unit_cost_ht, // On fige le CUMP au moment de la sortie
                    'project_id' => $intervention->project_id,
                    'project_phase_id' => $intervention->project_phase_id,
                    'user_id' => Auth::id(),
                ]);
            }
        }
    }
}
