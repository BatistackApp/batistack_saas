<?php

namespace App\Models\Fleet;

use App\Models\User;
use App\Observers\Fleet\VehicleCheckObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([VehicleCheckObserver::class])]
class VehicleCheck extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenants_id',
        'vehicle_id',
        'user_id',
        'vehicle_assignment_id',
        'type',
        'has_anomalie',
        'odometer_reading',
        'general_note',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(VehicleAssignment::class, 'vehicle_assignment_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(VehicleCheckResult::class);
    }

    protected function casts(): array
    {
        return [
            'has_anomalie' => 'boolean',
        ];
    }
}
