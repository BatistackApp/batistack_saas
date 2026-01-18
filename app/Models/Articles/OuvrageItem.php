<?php

namespace App\Models\Articles;

use App\Enums\Articles\UnitOfMeasure;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OuvrageItem extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function ouvrage(): BelongsTo
    {
        return $this->belongsTo(Ouvrage::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_of_measure' => UnitOfMeasure::class,
            'waste_percentage' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }
}
