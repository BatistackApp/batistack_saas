<?php

namespace App\Services\Locations;

use App\Models\Locations\RentalItem;
use Illuminate\Support\Carbon;

/**
 * Service de calcul financier (Moteur de prix).
 * Gère la dégressivité Jour / Semaine / Mois.
 */
class RentalCalculationService
{
    /**
     * Calcule le coût d'une ligne pour une période donnée.
     */
    public function calculateItemCost(RentalItem $item, Carbon $start, Carbon $end): float
    {
        $days = $this->getBillableDays($item, $start, $end);

        if ($days <= 0) return 0.0;

        // Logique de dégressivité standard BTP
        // 1. Si >= 20 jours -> Tarif Mois
        if ($days >= 20) {
            $months = $days / 30;
            $basePrice = $item->monthly_rate_ht * $months;
        }
        // 2. Si >= 5 jours -> Tarif Semaine
        elseif ($days >= 5) {
            $weeks = $days / 5; // Semaine BTP de 5 jours ouvrés souvent
            $basePrice = $item->weekly_rate_ht * $weeks;
        }
        // 3. Sinon -> Tarif Jour
        else {
            $basePrice = $item->daily_rate_ht * $days;
        }

        $totalWithInsurance = $basePrice * (1 + ($item->insurance_pct / 100));

        return round($totalWithInsurance * $item->quantity, 2);
    }

    /**
     * Calcule le nombre de jours facturables en tenant compte des weekends.
     */
    private function getBillableDays(RentalItem $item, Carbon $start, Carbon $end): int
    {
        if ($item->is_weekend_included) {
            return $start->diffInDays($end) + 1;
        }

        return $start->diffInDaysFiltered(function (Carbon $date) {
                return !$date->isWeekend();
            }, $end) + 1;
    }
}
