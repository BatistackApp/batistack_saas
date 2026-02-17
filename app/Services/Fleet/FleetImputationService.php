<?php

namespace App\Services\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleAssignment;
use Carbon\CarbonImmutable;
use DB;

class FleetImputationService
{
    /**
     * Impute les coûts d'un véhicule sur un projet durant sa période d'affectation.
     * Calcule le TCO complet : Consommation + Péages + Amortissement.
     */
    public function imputeCostsToProject(VehicleAssignment $assignment, ?CarbonImmutable $explicitEndDate = null): void
    {
        if (! $assignment->project_id) {
            return;
        }

        DB::transaction(function () use ($assignment) {
            $vehicle = $assignment->vehicle;
            $start = $assignment->started_at;
            $end = $explicitEndDate ?? ($assignment->ended_at ?? now());

            // 1. Récupération des péages réels durant l'affectation
            $tolls = (string) $vehicle->tolls()
                ->whereBetween('exit_at', [$start, $end])
                ->sum('amount_ht');

            // 2. Récupération du carburant/énergie réel
            $fuel = (string) $vehicle->consumptions()
                ->whereBetween('date', [$start, $end])
                ->sum('amount_ht');

            // 3. Calcul de l'amortissement théorique (Coût de possession)
            $depreciation = $this->calculateDepreciationCost($vehicle, $start, $end);

            // Somme totale via BCMath
            $totalToImpute = bcadd(bcadd($tolls, $fuel, 2), $depreciation, 2);

            // 4. Injection dans la table analytique des projets
            if (bccomp($totalToImpute, '0', 2) > 0) {
                DB::table('project_imputations')->insert([
                    'project_id' => $assignment->project_id,
                    'amount' => $totalToImpute,
                    'type' => 'fleet',
                    'metadata' => json_encode([
                        'assignment_id' => $assignment->id,
                        'details' => [
                            'fuel_ht' => (float) $fuel,
                            'tolls_ht' => (float) $tolls,
                            'depreciation_ht' => (float) $depreciation,
                        ],
                        'period' => [
                            'from' => $start->toDateTimeString(),
                            'to' => $end->toDateTimeString(),
                        ],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    /**
     * Calcule le coût d'amortissement linéaire pour la durée de l'affectation.
     * Hypothèse : Amortissement sur 5 ans (60 mois).
     */
    protected function calculateDepreciationCost(Vehicle $vehicle, CarbonImmutable $start, CarbonImmutable $end): string
    {
        $purchasePrice = (string) ($vehicle->purchase_price ?? '0');

        if (bccomp($purchasePrice, '0', 2) === 0) {
            return '0';
        }

        // Calcul du coût par jour (Prix / 1825 jours sur 5 ans)
        $dailyRate = bcdiv($purchasePrice, '1825', 4);

        $days = $start->diffInDays($end);
        if ($days <= 0) {
            $days = 1;
        }

        return bcmul($dailyRate, (string) $days, 2);
    }
}
