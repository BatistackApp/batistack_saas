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
        $currentCredit = $this->calculateProrataCredit($tenant, $currentCycle, $currentPrice);

        $subscription = $tenant->subscriptions()
            ->active()
            ->first();

        if (!$subscription) {
            return $newPrice;
        }

        $daysRemaining = $currentCycle->getDays() - $subscription->created_at->diffInDays(now());
        $newProrata = $this->calculateProrataNewPrice($newCycle, $newPrice, $daysRemaining);

        return max(0, $newProrata - $currentCredit);
    }
}
