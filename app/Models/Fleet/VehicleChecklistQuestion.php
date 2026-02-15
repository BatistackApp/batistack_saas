<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleChecklistQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'label',
        'description',
        'response_type',
        'is_mandatory',
        'requires_photo_on_anomaly',
        'sort_order',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(VehicleChecklistTemplate::class, 'template_id');
    }

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'requires_photo_on_anomaly' => 'boolean',
        ];
    }
}
