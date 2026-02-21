<?php

namespace App\Services\Locations;

use App\Enums\Locations\RentalStatus;
use App\Models\Locations\RentalContract;
use DB;

/**
 * Service d'imputation analytique (Lien avec le module Chantiers).
 */
class RentalCostImputationService
{
    public function __construct(
        protected RentalCalculationService $calculationService
    ) {}

    /**
     * Impute le coût journalier d'un contrat actif sur son projet/phase.
     * Appelé par le DailyRentalImputationJob.
     */
    public function imputeDailyCost(RentalContract $contract): void
    {
        if ($contract->status !== RentalStatus::ACTIVE) {
            return;
        }

        // Récupération de l'affectation active (Assignment)
        $activeAssignment = $contract->assignments()->whereNull('released_at')->latest()->first();
        // Si pas d'affectation spécifique, on fallback sur le projet racine du contrat
        $targetProjectId = $activeAssignment ? $activeAssignment->project_id : $contract->project_id;

        $yesterday = now()->subDay()->startOfDay();
        $today = now()->startOfDay();

        DB::transaction(function () use ($contract, $targetProjectId, $yesterday, $today) {
            foreach ($contract->items as $item) {
                $cost = $this->calculationService->calculateItemCost($item, $yesterday, $today);

                if ($cost > 0) {
                    DB::table('project_imputations')->insert([
                        'tenants_id' => $contract->tenants_id,
                        'project_id' => $targetProjectId,
                        'source_type' => 'rental_contract',
                        'source_id' => $contract->id,
                        'item_label' => $item->label,
                        'amount_ht' => $cost,
                        'imputed_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}
