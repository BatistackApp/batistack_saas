<?php

namespace App\Models\Core;

use App\Enums\Core\TenantStatus;
use App\Observers\Core\TenantsObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;

#[ObservedBy([TenantsObserver::class])]
class Tenants extends Model
{
    use HasFactory, SoftDeletes, Billable, Notifiable;
    protected $guarded = [];
    protected $keyType = 'int';
    protected string $billableKey = 'id';

    public function modules(): HasMany {
        return $this->hasMany(TenantModule::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id', 'id')
            ->where('type', self::class);
    }

    public function infoHolidays(): HasMany
    {
        return $this->hasMany(TenantInfoHolidays::class);
    }

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'status' => TenantStatus::class,
        ];
    }

    public function isActive(): bool {
        return $this->status === TenantStatus::Active->value;
    }


}
