<?php

namespace App\Models\Chantiers;

use App\Enums\Chantiers\CostCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChantierBudget extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    public function casts(): array
    {
        return [
            'category' => CostCategory::class,
            'planned_amount' => 'decimal:2',
        ];
    }
}
