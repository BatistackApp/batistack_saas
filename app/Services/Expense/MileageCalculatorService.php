<?php

namespace App\Services\Expense;

use App\Models\Expense\ExpenseItem;
use App\Models\Expense\ExpenseMileageScale;
use App\Models\User;

/**
 * Service spécialisé dans le calcul complexe des IK (Paliers URSSAF).
 */
class MileageCalculatorService
{
    /**
     * Calcule le montant des IK en tenant compte du cumul annuel de l'utilisateur.
     * Gère automatiquement le passage d'une tranche à une autre (Le "Split").
     */
    public function calculateForUser(User $user, int $vehiclePower, float $distanceToAdd, ?int $year = null): float
    {
        $year = $year ?? now()->year;

        // 1. Récupérer le cumul de distance déjà déclaré par l'utilisateur cette année
        $currentAnnualDistance = $this->getUserAnnualDistance($user, $year);

        // 2. Récupérer les barèmes applicables pour cette puissance et cette année
        $scales = ExpenseMileageScale::where('tenants_id', $user->tenants_id)
            ->where('vehicle_power', $vehiclePower)
            ->where('active_year', $year)
            ->orderBy('min_km', 'asc')
            ->get();

        if ($scales->isEmpty()) {
            return round($distanceToAdd * 0.60, 2); // Fallback taux standard
        }

        $totalAmount = 0;
        $remainingDistance = $distanceToAdd;
        $simulatedTotal = $currentAnnualDistance;

        // 3. Logique de ventilation par palier (Algorithme de Split)
        foreach ($scales as $scale) {
            // Si on a déjà dépassé le max de ce palier, on passe au suivant
            if ($scale->max_km !== null && $simulatedTotal >= $scale->max_km) {
                continue;
            }

            // Calcul de la part de distance qui tombe dans ce palier
            $distanceInThisScale = 0;

            if ($scale->max_km === null) {
                // Dernier palier (infini)
                $distanceInThisScale = $remainingDistance;
            } else {
                $availableInScale = $scale->max_km - $simulatedTotal;
                $distanceInThisScale = min($remainingDistance, $availableInScale);
            }

            if ($distanceInThisScale > 0) {
                $totalAmount += ($distanceInThisScale * (float) $scale->rate_per_km);

                // Note : On pourrait ajouter ici une logique pour le 'fixed_amount'
                // si le barème demande de l'ajouter dès qu'on entre dans la tranche.

                $remainingDistance -= $distanceInThisScale;
                $simulatedTotal += $distanceInThisScale;
            }

            if ($remainingDistance <= 0) {
                break;
            }
        }

        return round($totalAmount, 2);
    }

    /**
     * Calcule la distance totale validée ou soumise par l'utilisateur sur l'année.
     */
    public function getUserAnnualDistance(User $user, int $year): float
    {
        return (float) ExpenseItem::whereHas('report', function ($query) use ($user, $year) {
            $query->where('user_id', $user->id)
                ->whereYear('date', $year)
                ->whereIn('status', ['submitted', 'approved', 'paid']);
        })
            ->where('is_mileage', true)
            ->sum('distance_km');
    }
}
