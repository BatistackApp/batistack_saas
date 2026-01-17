<?php

namespace App\Models\Core;

use App\Enums\Core\BillingPeriod;
use App\Enums\Core\SubscriptionStatus;
use App\Observers\Core\SubscriptionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

#[ObservedBy([SubscriptionObserver::class])]
class TenantSubscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    protected function casts(): array
    {
        return [
            'billing_period' => BillingPeriod::class,
            'status' => SubscriptionStatus::class,
            'trial_ends_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active;
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Synchroniser l'abonnement avec la passerelle (Stripe/Cashier).
     * Méthode idempotente minimale.
     */
    public function syncWithGateway(): void
    {
        try {
            if (app()->bound(\App\Services\Billing\SubscriptionSyncService::class)) {
                $service = app(\App\Services\Billing\SubscriptionSyncService::class);
                if (method_exists($service, 'sync')) {
                    $service->sync($this);
                    Log::info('TenantSubscription::syncWithGateway delegated', ['subscription_id' => $this->id]);
                    return;
                }
            }

            Log::info('TenantSubscription::syncWithGateway fallback', ['subscription_id' => $this->id]);
        } catch (\Throwable $e) {
            Log::error('TenantSubscription::syncWithGateway failed', [
                'subscription_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
