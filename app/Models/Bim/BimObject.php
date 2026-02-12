<?php

namespace App\Models\Bim;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BimObject extends Model
{
    use HasFactory;

    protected $fillable = [
        'bim_model_id',
        'guid',
        'ifc_type',
        'label',
        'properties',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(BimModel::class, 'bim_model_id');
    }

    public function mappings(): MorphMany
    {
        return $this->morphMany(BimMapping::class, 'mappable');
    }

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }
}
