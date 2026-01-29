<?php

namespace App\Services\Core;

use App\Enums\Core\SubscriptionStatus;
use App\Models\Core\Tenants;
use Stripe\Event;

class StripeWebhookHandler
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private BillingHistoryService $historyService,
    ) {}

    public function handleInvoicePaymentFailed(Event $event): void
    {
        $invoice = $event->data->object;

        $tenant = Tenants::whereHas('subscriptions', function ($q) use ($invoice) {
            $q->where('stripe_id', $invoice->subscription);
        })->firstOrFail();

        // Mettre à jour le statut de l'abonnement
        $tenant->subscriptions()
            ->where('stripe_id', $invoice->subscription)
            ->update(['stripe_status' => SubscriptionStatus::PastDue->value]);

        // Enregistrer l'événement
        $this->historyService->logEvent(
            tenant: $tenant,
            eventType: 'payment_failed',
            amountCharged: $invoice->amount_due / 100,
            stripeInvoiceId: $invoice->id,
            description: "Échec de paiement de la facture {$invoice->number}",
            metadata: ['stripe_error' => $invoice->last_payment_error],
        );
    }

    public function handleCustomerSubscriptionDeleted(Event $event): void
    {
        $subscription = $event->data->object;

        $tenant = Tenants::whereHas('subscriptions', function ($q) use ($subscription) {
            $q->where('stripe_id', $subscription->id);
        })->firstOrFail();

        // Mettre à jour le statut
        $tenant->subscriptions()
            ->where('stripe_id', $subscription->id)
            ->update(['stripe_status' => SubscriptionStatus::Cancelled->value]);

        // Enregistrer l'événement
        $this->historyService->logEvent(
            tenant: $tenant,
            eventType: 'subscription_cancelled',
            stripeSubscriptionId: $subscription->id,
            description: 'Abonnement supprimé dans Stripe',
        );
    }

    public function handleCustomerSubscriptionUpdated(Event $event): void
    {
        $subscription = $event->data->object;

        $tenant = Tenants::whereHas('subscriptions', function ($q) use ($subscription) {
            $q->where('stripe_id', $subscription->id);
        })->firstOrFail();

        // Synchroniser le statut
        $status = match($subscription->status) {
            'past_due' => SubscriptionStatus::PastDue,
            'canceled' => SubscriptionStatus::Cancelled,
            'paused' => SubscriptionStatus::Paused,
            default => SubscriptionStatus::Active,
        };

        $tenant->subscriptions()
            ->where('stripe_id', $subscription->id)
            ->update(['stripe_status' => $status->value]);

        $this->historyService->logEvent(
            tenant: $tenant,
            eventType: 'subscription_updated',
            stripeSubscriptionId: $subscription->id,
            description: "Statut de l'abonnement mis à jour : {$status->getLabel()}",
        );
    }
}
