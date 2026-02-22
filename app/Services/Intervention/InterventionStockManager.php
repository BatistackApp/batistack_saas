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
        if (! $intervention->warehouse_id) {
            return;
        }

        foreach ($intervention->items as $item) {
            if ($item->article_id) {
                if (! $this->inventoryService->hasEnoughStock($item->article, $intervention->warehouse, $item->quantity)) {
                    throw new InsufficientStockException("Stock insuffisant dans le dépôt/camion pour l'article : {$item->label}");
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
                $this->stockService->recordOuvrageExit(
                    $item->ouvrage,
                    $intervention->warehouse,
                    (float) $item->quantity,
                    $intervention->project_id ?? 0
                );
            } elseif ($item->article_id) {
                \App\Models\Articles\StockMovement::create([
                    'tenants_id' => $intervention->tenants_id,
                    'article_id' => $item->article_id,
                    'warehouse_id' => $intervention->warehouse_id,
                    'serial_number_id' => $item->article_serial_number_id,
                    'type' => \App\Enums\Articles\StockMovementType::Exit,
                    'quantity' => $item->quantity,
                    'unit_cost_ht' => $item->unit_cost_ht,
                    'project_id' => $intervention->project_id,
                    'project_phase_id' => $intervention->project_phase_id,
                    'user_id' => Auth::id(),
                ]);
            }
        }
    }
}
