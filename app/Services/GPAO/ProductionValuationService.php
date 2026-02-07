<?php

namespace App\Services\GPAO;

use App\Enums\Articles\StockMovementType;
use App\Enums\GPAO\WorkOrderStatus;
use App\Exceptions\GPAO\WorkOrderLockedException;
use App\Models\Articles\StockMovement;
use App\Models\GPAO\WorkOrder;
use App\Services\Articles\InventoryService;
use Auth;
use DB;

/**
 * Service de valorisation et de clôture de production.
 */
class ProductionValuationService
{
    public function __construct(protected InventoryService $inventoryService) {}

    /**
     * Clôture l'OF, calcule le coût de revient et fait entrer le produit fini en stock.
     */
    public function finalizeWorkOrder(WorkOrder $wo, ?float $quantityProduced = null): void
    {
        if ($wo->status === WorkOrderStatus::Completed) {
            throw new WorkOrderLockedException("Cet OF est déjà clôturé.");
        }

        DB::transaction(function () use ($wo, $quantityProduced) {
            $wo->load(['components', 'operations.workCenter']);

            // 1. Calcul des coûts réels engagés
            $materialCost = $wo->components->sum(fn($c) => $c->quantity_consumed * $c->unit_cost_ht);
            $machineCost = $wo->operations->sum(fn($op) => ($op->time_actual_minutes / 60) * $op->workCenter->hourly_rate);
            $totalCost = $materialCost + $machineCost;

            // 2. Détermination de la quantité finale
            $finalQty = $quantityProduced ?? $wo->quantity_planned;

            // 3. Mise à jour de l'OF
            $wo->update([
                'status' => WorkOrderStatus::Completed,
                'actual_end_at' => now(),
                'total_cost_ht' => $totalCost,
                'quantity_produced' => $finalQty
            ]);

            // 4. Calcul du coût de revient unitaire (spread cost)
            $unitCost = $finalQty > 0 ? ($totalCost / $finalQty) : 0;

            // 5. Entrée en stock de l'ouvrage produit
            StockMovement::create([
                'tenants_id' => $wo->tenants_id,
                'ouvrage_id' => $wo->ouvrage_id,
                'warehouse_id' => $wo->warehouse_id,
                'type' => StockMovementType::Entry,
                'quantity' => $finalQty,
                'unit_cost_ht' => $unitCost,
                'user_id' => Auth::id(),
            ]);

            // 6. Mise à jour de la valeur moyenne du stock (CUMP)
            $this->inventoryService->updateCump($wo->ouvrage, (float) $finalQty, $unitCost);
        });
    }
}
