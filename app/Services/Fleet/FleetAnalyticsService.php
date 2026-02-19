<?php

namespace App\Services\Fleet;

use App\Models\Fleet\Vehicle;
use Carbon\CarbonImmutable;
use DB;

class FleetAnalyticsService
{
    /**
     * Calcule le coût total de possession (TCO) pour un véhicule.
     * Basé sur les consommations, les péages et l'amortissement.
     */
    public function getVehicleTco(Vehicle $vehicle, ?CarbonImmutable $startDate = null, ?CarbonImmutable $endDate = null): array
    {
        $startDate ??= CarbonImmutable::now()->subYear();
        $endDate ??= CarbonImmutable::now();
        $depreciation = '0';

        // 1. Coûts de consommation (Carburant/Énergie)
        $energyCosts = (string) $vehicle->consumptions()
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount_ht');

        // 2. Coûts de péages
        $tollCosts = (string) $vehicle->tolls()
            ->whereBetween('exit_at', [$startDate, $endDate])
            ->sum('amount_ht');

        // 3. NOUVEAU : Coûts de Maintenance (Préventive + Curative)
        $maintenanceCosts = $vehicle->maintenances()
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->sum(DB::raw('cost_parts + cost_labor'));

        // 3. Estimation d'amortissement (Exemple: 20% par an)
        $purchasePrice = (string) ($vehicle->purchase_price ?? 0);
        $yearsOwned = $vehicle->purchase_date ? $vehicle->purchase_date->diffInYears(now(), true) : 1;

        if (bccomp($purchasePrice, '0', 2) > 0) {
            $daysInPeriod = $startDate->diffInDays($endDate);
            $dailyDepreciationRate = bcdiv($purchasePrice, '1825', 4); // Amortissement sur 5 ans (1825 jours)
            $depreciation = bcmul($dailyDepreciationRate, (string) $daysInPeriod, 2);
        }

        $totalTco = bcadd(bcadd($energyCosts, $tollCosts, 2), $depreciation, 2);
        $totalTco += $maintenanceCosts; // Ajouter les coûts de maintenance au TCO total

        // 4. Coût au kilomètre
        $kmTraveled = (float) $this->getDistanceTraveled($vehicle, $startDate, $endDate);
        $costPerKm = ($kmTraveled > 0) ? (float) bcdiv($totalTco, (string) $kmTraveled, 4) : 0;

        return [
            'energy_ht' => (float) $energyCosts,
            'tolls_ht' => (float) $tollCosts,
            'maintenance_ht' => (float) $maintenanceCosts,
            'depreciation_est' => (float) $depreciation,
            'total_tco_ht' => (float) $totalTco,
            'km_traveled' => $kmTraveled,
            'cost_per_km' => $costPerKm,
            'downtime_total_hours' => $vehicle->maintenances()->whereBetween('completed_at', [$startDate, $endDate])->sum('downtime_hours'),
        ];
    }

    /**
     * Calcule la consommation moyenne (L/100km) entre deux pleins.
     */
    public function calculateAverageConsumption(Vehicle $vehicle): float
    {
        $consumptions = $vehicle->consumptions()
            ->orderBy('odometer_reading', 'asc')
            ->get();

        if ($consumptions->count() < 2) {
            return 0.0;
        }

        $totalLiters = $consumptions->skip(1)->sum('quantity');
        $distance = $consumptions->last()->odometer_reading - $consumptions->first()->odometer_reading;

        if ($distance <= 0) {
            return 0.0;
        }

        return (float) round(($totalLiters * 100) / $distance, 2);
    }

    private function getDistanceTraveled(Vehicle $vehicle, CarbonImmutable $start, CarbonImmutable $end): float
    {
        // 1. On récupère le relevé le plus récent dans la période
        $lastEntry = $vehicle->consumptions()
            ->where('date', '<=', $end)
            ->orderBy('date', 'desc')
            ->first();

        if (! $lastEntry) {
            return 0.0;
        }

        // 2. On cherche le relevé juste AVANT le début de la période
        $previousEntry = $vehicle->consumptions()
            ->where('date', '<', $start)
            ->orderBy('date', 'desc')
            ->first();

        // 3. Si pas de relevé avant, on prend le premier relevé DE LA période
        // mais on le compare au kilométrage de départ du véhicule (si disponible)
        if (! $previousEntry) {
            $firstEntryInPeriod = $vehicle->consumptions()
                ->where('date', '>=', $start)
                ->orderBy('date', 'asc')
                ->first();

            // Ici, on suppose que 'current_odometer' à la création du véhicule
            // est notre point zéro. Idéalement, il faudrait un champ 'initial_odometer'.
            $startMileage = $vehicle->initial_odometer ?? 0;

            // Si vous utilisez 'current_odometer' comme base lors de la création :
            // Note : Attention si le véhicule a roulé depuis, cette valeur a pu changer.
            return (float) ($lastEntry->odometer_reading - $startMileage);
        }
        dd($lastEntry->odometer_reading, $previousEntry->odometer_reading);

        return (float) ($previousEntry->odometer_reading - $lastEntry->odometer_reading);
    }
}
