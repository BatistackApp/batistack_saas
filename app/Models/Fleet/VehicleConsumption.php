<?php

namespace App\Models\Fleet;

use App\Observers\Fleet\VehicleConsumptionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([VehicleConsumptionObserver::class])]
class VehicleConsumption extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity' => 'decimal:2',
            'amount_ht' => 'decimal:2',
        ];
    }
}
