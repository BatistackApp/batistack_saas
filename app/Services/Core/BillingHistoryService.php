<?php

namespace App\Services\Core;

use App\Models\Core\BillingHistory;
use App\Models\Core\Tenants;
use Illuminate\Pagination\Paginator;

class BillingHistoryService
{
    public function logEvent(
        Tenants $tenant,
        string $eventType,
        ?string $oldPlanId = null,
        ?string $newPlanId = null,
        ?float $amountCharged = null,
        ?string $stripeSubscriptionId = null,
        ?string $stripeInvoiceId = null,
        ?string $description = null,
        ?array $metadata = null,
    ): BillingHistory {
        return BillingHistory::create([
            'tenants_id' => $tenant->id,
            'event_type' => $eventType,
            'old_plan_id' => $oldPlanId,
            'new_plan_id' => $newPlanId,
            'amount_charged' => $amountCharged,
            'currency' => 'EUR',
            'description' => $description,
            'stripe_subscription_id' => $stripeSubscriptionId,
            'stripe_invoice_id' => $stripeInvoiceId,
            'metadata' => $metadata,
        ]);
    }

    public function getEventHistory(Tenants $tenant, int $limit = 50): Paginator
    {
        return BillingHistory::where('tenants_id', $tenant->id)
            ->orderByDesc('created_at')
            ->simplePaginate($limit);
    }

    public function getTotalChargedThisMonth(Tenants $tenant): float
    {
        return BillingHistory::where('tenants_id', $tenant->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount_charged') ?? 0;
    }

    public function getTotalChargedThisYear(Tenants $tenant): float
    {
        return BillingHistory::where('tenants_id', $tenant->id)
            ->whereYear('created_at', now()->year)
            ->sum('amount_charged') ?? 0;
    }
}
