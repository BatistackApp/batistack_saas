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
    public function finalizeWorkOrder(WorkOrder $wo): void
    {
        if ($wo->status === WorkOrderStatus::Completed) {
            throw new WorkOrderLockedException("Cet OF est déjà clôturé.");
        }

        DB::transaction(function () use ($wo) {
            $wo->load(['components', 'operations.workCenter']);

            // 1. Calcul du coût des matières
            $materialCost = $wo->components->sum(function ($c) {
                return $c->quantity_consumed * $c->unit_cost_ht;
            });

            // 2. Calcul du coût des postes de charge (Heures machine)
            $machineCost = $wo->operations->sum(function ($op) {
                $hours = $op->time_actual_minutes / 60;
                return $hours * $op->workCenter->hourly_rate;
            });

            $totalCost = $materialCost + $machineCost;

            // 3. Mise à jour de l'OF
            $wo->update([
                'status' => WorkOrderStatus::Completed,
                'actual_end_at' => now(),
                'total_cost_ht' => $totalCost,
                'quantity_produced' => $wo->quantity_planned // Par défaut, tout est produit
            ]);

            // 4. Entrée en stock du produit fini (l'Ouvrage)
            $unitProductionCost = $totalCost / $wo->quantity_planned;

            // Création du mouvement d'entrée
            StockMovement::create([
                'tenants_id' => $wo->tenants_id,
                'article_id' => null, // C'est un ouvrage qui entre en stock
                'ouvrage_id' => $wo->ouvrage_id,
                'warehouse_id' => $wo->warehouse_id,
                'type' => StockMovementType::Entry,
                'quantity' => $wo->quantity_produced,
                'unit_cost_ht' => $unitProductionCost,
                'user_id' => Auth::id(),
            ]);

            // 5. Mise à jour du CUMP de l'Ouvrage produit via l'InventoryService
            $this->inventoryService->updateCump($wo->ouvrage, (float) $wo->quantity_produced, $unitProductionCost);
        });
    }
}
