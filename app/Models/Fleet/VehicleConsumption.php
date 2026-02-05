<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
