<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleCheckResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_check_id',
        'question_id',
        'value',
        'anomaly_description',
        'photo_path',
        'is_anomaly',
    ];

    public function check(): BelongsTo
    {
        return $this->belongsTo(VehicleCheck::class, 'vehicle_check_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(VehicleChecklistQuestion::class, 'question_id');
    }

    protected function casts(): array
    {
        return [
            'is_anomaly' => 'boolean',
        ];
    }
}
