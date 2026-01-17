<?php

namespace App\Services\Billing;

use App\Models\Core\TenantSubscription;
use Illuminate\Support\Facades\Log;

class SubscriptionSyncService
{
    /**
     * Synchronise l'abonnement avec Stripe/Cashier.
     */
    public function sync(TenantSubscription $subscription): void
    {
        try {
            $tenant = $subscription->tenant;

            if ($tenant === null) {
                Log::warning('SubscriptionSync: tenant not found', ['subscription_id' => $subscription->id]);

                return;
            }

            // Exemple : vérifier si un modèle Stripe existe via Cashier
            // Si le tenant utilise Laravel Cashier, on peut vérifier l'abonnement Stripe
            if (method_exists($tenant, 'subscriptions')) {
                $stripeSubscription = $tenant->subscriptions()
                    ->where('stripe_id', $subscription->stripe_subscription_id)
                    ->first();

                if ($stripeSubscription !== null && method_exists($stripeSubscription, 'asStripeSubscription')) {
                    $remoteSubscription = $stripeSubscription->asStripeSubscription();

                    // Mettre à jour l'état local selon la source externe
                    $subscription->status = $remoteSubscription->status;
                    $subscription->ends_at = $remoteSubscription->ended_at ?
                        \Illuminate\Support\Carbon::createFromTimestamp($remoteSubscription->ended_at) :
                        null;
                    $subscription->save();

                    Log::info('SubscriptionSync: synced from Stripe', [
                        'subscription_id' => $subscription->id,
                        'status' => $subscription->status,
                    ]);

                    return;
                }
            }

            // Fallback : log minimal
            Log::debug('SubscriptionSync: no Stripe subscription found or Cashier not configured', [
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('SubscriptionSync: sync failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
