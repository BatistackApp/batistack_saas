<?php

namespace App\Jobs\Core;

use App\Models\Core\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSubscriptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public TenantSubscription $subscription) {}

    public function handle(): void
    {
        $subscription = $this->subscription->fresh();
        if ($subscription === null) {
            Log::warning('SyncSubscriptionsJob: subscription not found', ['id' => $this->subscription->id]);

            return;
        }

        // Exemple d'extension : délégué à une méthode du modèle/service si disponible
        if (method_exists($subscription, 'syncWithGateway') === true) {
            try {
                $subscription->syncWithGateway();
                Log::info('SyncSubscriptionsJob: synced', ['subscription_id' => $subscription->id]);
            } catch (\Throwable $e) {
                Log::error('SyncSubscriptionsJob: sync failed', ['subscription_id' => $subscription->id, 'error' => $e->getMessage()]);
            }

            return;
        }
    }
}
