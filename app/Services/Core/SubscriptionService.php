<?php

namespace App\Services\Core;

use App\Enums\Core\BillingCycle;
use App\Models\Core\Tenants;

class SubscriptionService
{
    public function getActiveSubscription(Tenants $tenant): ?object
    {
        return $tenant->subscriptions()
            ->active()
            ->first();
    }

    public function getSubscriptionCost(BillingCycle $cycle, ?float $monthlyPrice = null): float
    {
        if (! $monthlyPrice) {
            return 0;
        }

        return (float) match ($cycle) {
            BillingCycle::Monthly => $monthlyPrice,
            BillingCycle::Quarterly => $monthlyPrice * 3,
            BillingCycle::Yearly => $monthlyPrice * 12,
        };
    }

    public function isSubscriptionExpired(Tenants $tenant): bool
    {
        $subscription = $tenant->subscriptions()
            ->where('stripe_status', 'active')
            ->first();

        if (! $subscription) {
            return false;
        }

        return $subscription->ends_at !== null && $subscription->ends_at->isPast();
    }

    public function isSubscriptionActive(Tenants $tenant): bool
    {
        $subscription = $this->getActiveSubscription($tenant);

        return $subscription !== null && ! $this->isSubscriptionExpired($tenant);
    }
}
