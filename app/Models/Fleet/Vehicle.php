<?php

namespace App\Models\Fleet;

use App\Enums\Fleet\FuelType;
use App\Enums\Fleet\VehicleType;
use App\Observers\Fleet\VehicleObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([VehicleObserver::class])]
class Vehicle extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => VehicleType::class,
            'fuel_type' => FuelType::class,
            'is_active' => 'boolean',
            'current_odometer' => 'decimal:2',
            'purchase_date' => 'date',
            'last_external_sync_at' => 'datetime',
            'is_available' => 'boolean',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(VehicleInspection::class);
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(VehicleConsumption::class);
    }

    public function tolls(): HasMany
    {
        return $this->hasMany(VehicleToll::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(VehicleAssignment::class)->whereNull('ended_at')->latestOfMany('started_at');
    }
}
