<?php

namespace App\Jobs\Core;

use App\Models\Core\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ActivatePlanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public TenantSubscription $subscription) {}

    public function handle(): void
    {
        $subscription = $this->subscription->fresh();

        if ($subscription === null) {
            Log::warning('ActivatePlanJob: subscription not found', ['id' => $this->subscription->id]);

            return;
        }

        $tenant = $subscription->tenant;

        if ($tenant === null) {
            Log::warning('ActivatePlanJob: tenant missing', ['subscription_id' => $subscription->id]);

            return;
        }

        try {
            if (method_exists($tenant, 'enableModulesForPlan') === true) {
                $tenant->enableModulesForPlan($subscription->plan_id);
                Log::info('ActivatePlanJob: modules enabled', ['tenant_id' => $tenant->id, 'plan_id' => $subscription->plan_id]);
            } else {
                Log::debug('ActivatePlanJob: tenant->enableModulesForPlan not implemented', ['tenant_id' => $tenant->id]);
            }
        } catch (\Throwable $e) {
            Log::error('ActivatePlanJob: activation failed', ['tenant_id' => $tenant->id, 'error' => $e->getMessage()]);
        }
    }
}
