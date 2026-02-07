<?php

namespace App\Services\Locations;

use App\Enums\Locations\RentalStatus;
use App\Models\HR\Employee;
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
        if ($contract->status !== RentalStatus::ACTIVE) return;

        $yesterday = now()->subDay()->startOfDay();
        $today = now()->startOfDay();

        DB::transaction(function () use ($contract, $yesterday, $today) {
            foreach ($contract->items as $item) {
                $cost = $this->calculationService->calculateItemCost($item, $yesterday, $today);

                if ($cost > 0) {
                    // Création de l'écriture de coût réel
                    // On assume une table project_costs ou équivalent que ProjectBudgetService agrège

                    DB::table('project_imputations')->insert([
                        'project_id' => $contract->project_id,
                        'type' => 'rental',
                        'amount' => $cost
                    ]);
                }
            }
        });
    }
}
