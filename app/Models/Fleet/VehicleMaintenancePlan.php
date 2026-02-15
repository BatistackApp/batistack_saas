<?php

namespace App\Models\Fleet;

use App\Enums\Fleet\VehicleType;
use App\Models\Core\Tenants;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleMaintenancePlan extends Model
{
    use HasFactory, SoftDeletes, HasTenant;

    protected $fillable = [
        'tenants_id',
        'name',
        'vehicle_type',
        'interval_km',
        'interval_hours',
        'interval_month',
        'operations',
        'is_active',
    ];
    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'operations' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
