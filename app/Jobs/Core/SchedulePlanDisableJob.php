<?php

namespace App\Jobs\Core;

use App\Models\Core\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SchedulePlanDisableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public TenantSubscription $subscription)
    {
    }

    public function handle(): void
    {
        $subscription = $this->subscription->fresh();

        if ($subscription === null) {
            Log::warning('SchedulePlanDisableJob: subscription not found', ['id' => $this->subscription->id]);
            return;
        }

        $tenant = $subscription->tenant;

        if ($tenant === null) {
            Log::warning('SchedulePlanDisableJob: tenant missing', ['subscription_id' => $subscription->id]);
            return;
        }

        try {
            if (method_exists($tenant, 'schedulePlanDisable') === true) {
                $tenant->schedulePlanDisable($subscription);
                Log::info('SchedulePlanDisableJob: scheduled plan disable', ['tenant_id' => $tenant->id, 'subscription_id' => $subscription->id]);
            } else {
                Log::debug('SchedulePlanDisableJob: tenant->schedulePlanDisable not implemented', ['tenant_id' => $tenant->id]);
            }
        } catch (\Throwable $e) {
            Log::error('SchedulePlanDisableJob: scheduling failed', ['subscription_id' => $subscription->id, 'error' => $e->getMessage()]);
        }
    }
}
