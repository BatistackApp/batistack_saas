<?php

namespace App\Services\Billing;

use App\Models\Core\Plan;
use App\Models\Core\Tenant;
use Illuminate\Support\Facades\Log;

class PlanActivatorService
{
    /**
     * Active les modules associés au plan pour le tenant.
     */
    public function activateForTenant(Tenant $tenant, int|string $planId): void
    {
        try {
            $plan = Plan::findOrFail($planId);

            // Récupérer les modules du plan
            $moduleIds = $plan->modules()->pluck('id')->toArray();

            if (empty($moduleIds)) {
                Log::debug('PlanActivator: no modules found for plan', ['plan_id' => $planId]);

                return;
            }

            // Synchroniser les modules : activer les nouveaux, garder les anciens
            $tenant->modules()->syncWithoutDetaching(
                collect($moduleIds)->mapWithKeys(fn ($id) => [
                    $id => [
                        'is_active' => true,
                        'subscribed_at' => now(),
                    ],
                ])->toArray()
            );

            Log::info('PlanActivator: modules activated', [
                'tenant_id' => $tenant->id,
                'plan_id' => $planId,
                'module_count' => count($moduleIds),
            ]);
        } catch (\Throwable $e) {
            Log::error('PlanActivator: activation failed', [
                'tenant_id' => $tenant->id,
                'plan_id' => $planId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
