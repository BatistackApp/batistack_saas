<?php

namespace App\Models\Core;

use App\Enums\Core\TenantStatus;
use App\Observers\Core\TenantsObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([TenantsObserver::class])]
class Tenants extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function modules(): HasMany {
        return $this->hasMany(TenantModule::class);
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
