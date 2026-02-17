<?php

namespace App\Models\Fleet;

use App\Enums\Fleet\MaintenanceStatus;
use App\Enums\Fleet\MaintenanceType;
use App\Models\User;
use App\Observers\Fleet\VehicleMaintenanceObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([VehicleMaintenanceObserver::class])]
class VehicleMaintenance extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenants_id',
        'vehicle_id',
        'vehicle_maintenance_plan_id',
        'reported_by',
        'technician_name',
        'maintenance_type',
        'maintenance_status',
        'description',
        'resolution_notes',
        'odometer_reading',
        'hours_reading',
        'cost_parts',
        'cost_labor',
        'reported_at',
        'scheduled_at',
        'started_at',
        'completed_at',
        'downtime_hours',
        'internal_reference',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_type' => MaintenanceType::class,
            'maintenance_status' => MaintenanceStatus::class,
            'reported_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_cost' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(VehicleMaintenancePlan::class, 'vehicle_maintenance_plan_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
