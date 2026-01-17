<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

class Tenant extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class)->latest();
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'tenant_modules')
            ->withPivot(['billing_period', 'is_active', 'stripe_subscription_id', 'subscribed_at', 'expires_at'])
            ->withTimestamps();
    }

    public function activeModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('is_active', true);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function billingInfo(): HasOne
    {
        return $this->hasOne(TenantBillingInfo::class);
    }

    protected function casts(): array
    {
        return [
            'subscription_expires_at' => 'timestamp',
            'is_active' => 'boolean',
        ];
    }

    public function hasModule(string $moduleSlug): bool
    {
        return $this->activeModules()->where('slug', $moduleSlug)->exists();
    }

    /**
     * Activer les modules/entitlements selon un plan.
     */
    public function enableModulesForPlan(int|string $planId): void
    {
        try {
            if (app()->bound(\App\Services\Billing\PlanActivatorService::class)) {
                $service = app(\App\Services\Billing\PlanActivatorService::class);
                if (method_exists($service, 'activateForTenant')) {
                    $service->activateForTenant($this, $planId);
                    return;
                }
            }

            Log::info('Tenant::enableModulesForPlan fallback', ['tenant_id' => $this->id, 'plan_id' => $planId]);
        } catch (\Throwable $e) {
            Log::error('Tenant::enableModulesForPlan failed', [
                'tenant_id' => $this->id,
                'plan_id' => $planId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Planifier la désactivation d'un plan (ex: en fin d'abonnement).
     */
    public function schedulePlanDisable(TenantSubscription $subscription): void
    {
        try {
            if (method_exists($this, 'scheduleDisableForSubscription')) {
                $this->scheduleDisableForSubscription($subscription);
                return;
            }

            Log::info('Tenant::schedulePlanDisable fallback', [
                'tenant_id' => $this->id,
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Tenant::schedulePlanDisable failed', [
                'tenant_id' => $this->id,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
