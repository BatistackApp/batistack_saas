<?php

namespace App\Services\GPAO;

use App\Enums\Articles\StockMovementType;
use App\Exceptions\GPAO\InsufficientMaterialException;
use App\Models\Articles\StockMovement;
use App\Models\GPAO\WorkOrder;
use App\Models\GPAO\WorkOrderComponent;
use App\Models\User;
use App\Notifications\GPAO\StockShortageNotification;
use App\Services\Articles\InventoryService;
use App\Services\Articles\StockMovementService;
use Auth;
use DB;
use Illuminate\Support\Facades\Notification;

/**
 * Service d'orchestration de la production (Lien avec le Stock).
 */
class ProductionOrchestrator
{
    public function __construct(
        protected StockMovementService $stockService,
        protected InventoryService $inventoryService
    ) {}

    /**
     * Initialise un OF en explosant la nomenclature de l'ouvrage lié.
     */
    public function initializeFromOuvrage(WorkOrder $wo): void
    {
        $wo->load('ouvrage.components');

        if ($wo->ouvrage->components->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($wo) {
            foreach ($wo->ouvrage->components as $component) {
                $qtyNeeded = $component->pivot->quantity_needed * $wo->quantity_planned;

                WorkOrderComponent::create([
                    'work_order_id' => $wo->id,
                    'article_id' => $component->id,
                    'label' => $component->name,
                    'quantity_planned' => $qtyNeeded,
                    'unit_cost_ht' => $component->cump_ht, // On fige le CUMP au lancement
                ]);
            }
        });
    }

    /**
     * Vérifie la disponibilité des stocks et notifie en cas de rupture.
     * Cette méthode doit être appelée lors du passage au statut 'PLANNED'.
     */
    public function validateStockAvailability(WorkOrder $wo): bool
    {
        $wo->load('components.article');
        $hasShortage = false;

        foreach ($wo->components as $component) {
            if (! $this->inventoryService->hasEnoughStock($component->article, $wo->warehouse, $component->quantity_planned)) {
                $hasShortage = true;

                // AUTOMATISATION : Notification immédiate de rupture pour l'OF
                $recipients = User::permission('gpao.manage')->get();
                Notification::send($recipients, new StockShortageNotification($wo, $component->label));
            }
        }

        return ! $hasShortage;
    }

    /**
     * Consomme les matières premières pour cet OF.
     */
    public function consumeComponents(WorkOrder $wo): void
    {
        // On vérifie une dernière fois avant de sortir les pièces
        if (! $this->validateStockAvailability($wo)) {
            throw new InsufficientMaterialException('Impossible de consommer les composants : stock insuffisant.', 422);
        }

        foreach ($wo->components as $component) {
            // Création du mouvement de sortie pour chaque composant
            StockMovement::create([
                'tenants_id' => $wo->tenants_id,
                'article_id' => $component->article_id,
                'warehouse_id' => $wo->warehouse_id, // Ou un dépôt "Matières" spécifique
                'type' => StockMovementType::Exit,
                'quantity' => $component->quantity_planned,
                'unit_cost_ht' => $component->unit_cost_ht,
                'project_id' => $wo->project_id,
                'project_phase_id' => $wo->project_phase_id,
                'user_id' => Auth::id(),
            ]);

            $component->update(['quantity_consumed' => $component->quantity_planned]);
        }
    }
}
