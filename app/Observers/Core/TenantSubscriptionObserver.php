<?php

namespace App\Observers\Core;

use App\Enums\Core\TenantStatus;
use App\Models\Core\Tenants;
use App\Services\Core\BillingHistoryService;
use Laravel\Cashier\Subscription;
use Log;

class TenantSubscriptionObserver
{
    public function __construct(
        private BillingHistoryService $historyService,
    ) {}

    public function created(Subscription $subscription): void
    {
        $tenant = Tenants::find($subscription->billable_id);
        if (! $tenant) {
            return;
        }

        Log::info('Subscription created via Cashier', [
            'tenant_id' => $tenant->id,
            'stripe_subscription_id' => $subscription->stripe_id,
            'plan' => $subscription->stripe_price,
        ]);

        $this->historyService->logEvent(
            tenant: $tenant,
            eventType: 'subscription_created',
            stripeSubscriptionId: $subscription->stripe_id,
            description: "Abonnement créé : {$subscription->stripe_price}",
            metadata: [
                'stripe_id' => $subscription->stripe_id,
                'plan' => $subscription->stripe_price,
                'quantity' => $subscription->quantity,
            ],
        );
    }

    public function updated(Subscription $subscription): void
    {
        if (! $subscription->isDirty('stripe_status')) {
            return;
        }

        $tenant = Tenants::find($subscription->billable_id);

        if (! $tenant) {
            Log::warning('Tenant not found for subscription', [
                'subscription_id' => $subscription->stripe_id,
                'billable_id' => $subscription->billable_id,
            ]);

            return;
        }

        if ($subscription->stripe_status === 'past_due') {
            $tenant->update(['status' => TenantStatus::Suspended]);
        }

        // ✅ Réactiver le tenant si le statut repasse à "active"
        if ($subscription->stripe_status === 'active' && $tenant->status === TenantStatus::Suspended) {
            $tenant->update(['status' => TenantStatus::Active]);
        }


        if ($subscription->isDirty('stripe_status')) {
            Log::info('Subscription status changed', [
                'tenant_id' => $tenant->id,
                'old_status' => $subscription->getOriginal('stripe_status'),
                'new_status' => $subscription->stripe_status,
            ]);

            $this->historyService->logEvent(
                tenant: $tenant,
                eventType: 'subscription_status_changed',
                stripeSubscriptionId: $subscription->stripe_id,
                description: "Statut changé : {$subscription->getOriginal('stripe_status')} → {$subscription->stripe_status}",
                metadata: [
                    'old_status' => $subscription->getOriginal('stripe_status'),
                    'new_status' => $subscription->stripe_status,
                ],
            );
        }
    }

    public function deleted(Subscription $subscription): void
    {
        $tenant = Tenants::find($subscription->billable_id);

        if (! $tenant) {
            return;
        }

        Log::warning('Subscription deleted', [
            'tenant_id' => $tenant->id,
            'stripe_subscription_id' => $subscription->stripe_id,
        ]);

        $this->historyService->logEvent(
            tenant: $tenant,
            eventType: 'subscription_deleted',
            stripeSubscriptionId: $subscription->stripe_id,
            description: 'Abonnement supprimé',
        );
    }
}
