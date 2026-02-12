<?php

namespace App\Models\Bim;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BimMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'bim_object_id',
        'color_override',
        'metadata',
    ];

    public function bimObject(): BelongsTo
    {
        return $this->belongsTo(BimObject::class);
    }

    public function mappable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
