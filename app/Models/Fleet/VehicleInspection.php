<?php

namespace App\Models\Fleet;

use App\Enums\Fleet\InspectionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleInspection extends Model
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
            'inspection_date' => 'date',
            'next_due_date' => 'date',
            'type' => InspectionType::class,
        ];
    }
}
