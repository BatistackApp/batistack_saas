<?php

namespace App\Models\Core;

use App\Enums\Core\PlanPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'priority' => PlanPriority::class,
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'is_active' => 'boolean',
            'features' => 'array',
        ];
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class)
            ->withPivot('is_included')
            ->withTimestamps();
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_modules')
            ->withPivot(['billing_period', 'is_active', 'stripe_subscription_id', 'subscribed_at', 'expires_at'])
            ->withTimestamps();
    }
}
