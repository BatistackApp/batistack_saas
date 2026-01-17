<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
}
