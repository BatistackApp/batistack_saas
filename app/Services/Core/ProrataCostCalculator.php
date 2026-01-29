<?php

namespace App\Services\Core;

use App\Enums\Core\BillingCycle;
use App\Models\Core\Tenants;

class ProrataCostCalculator
{
    public function calculateProrataCredit(
        Tenants $tenant,
        BillingCycle $currentCycle,
        float $currentPrice,
    ): float {
        $subscription = $tenant->subscriptions()
            ->active()
            ->first();

        if (!$subscription) {
            return 0;
        }

        $daysUsed = $subscription->created_at->diffInDays(now());
        $daysInCycle = $currentCycle->getDays();

        // Crédit pro-rata = (Prix mensuel / Jours du cycle) × Jours utilisés
        return ($currentPrice / $daysInCycle) * $daysUsed;
    }

    public function calculateProrataNewPrice(
        BillingCycle $newCycle,
        float $newPrice,
        int $daysRemaining,
    ): float {
        // Prix journalier × Jours restants
        $dailyRate = $newPrice / $newCycle->getDays();

        return $dailyRate * $daysRemaining;
    }

    public function calculateUpgradeAdjustment(
        Tenants $tenant,
        BillingCycle $currentCycle,
        float $currentPrice,
        BillingCycle $newCycle,
        float $newPrice,
    ): float {
        $subscription = $tenant->subscriptions()
            ->where('stripe_status', 'active')
            ->first();

        if (! $subscription) {
            return 0;
        }

        $currentPrice = (float) $currentPrice;
        $newPrice = (float) $newPrice;

        $daysInCycle = $currentCycle === BillingCycle::Monthly ? 30 : 365;
        $daysUsed = $subscription->created_at->diffInDays(now());
        $daysRemaining = max(0, $daysInCycle - $daysUsed);

        // Crédit non utilisé au prix actuel
        $creditForCurrentPrice = ($currentPrice / $daysInCycle) * $daysRemaining;

        // Coût pour le nouveau prix sur les jours restants
        $costForNewPrice = ($newPrice / $daysInCycle) * $daysRemaining;

        // L'ajustement = crédit actuel - nouveau coût
        // Downgrade (new < current) = résultat négatif ✓
        return $creditForCurrentPrice - $costForNewPrice;
    }
}
