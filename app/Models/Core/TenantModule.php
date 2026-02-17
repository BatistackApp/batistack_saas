<?php

namespace App\Models\Core;

use App\Enums\Core\TenantModuleStatus;
use App\Observers\Core\TenantModuleObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([TenantModuleObserver::class])]
class TenantModule extends Model
{
    use HasFactory, HasTenant;

    protected $guarded = [];

    protected $foreignKey = 'tenants_id';

    public function module(): BelongsTo
    {
        return $this->belongsTo(ModuleCatalog::class, 'module_id');
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'config' => 'array',
            'status' => TenantModuleStatus::class,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === TenantModuleStatus::Active->value &&
            $this->starts_at <= now() &&
            ($this->ends_at === null || $this->ends_at > now());
    }
}
