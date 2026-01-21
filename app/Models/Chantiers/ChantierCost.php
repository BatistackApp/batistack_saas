<?php

namespace App\Models\Chantiers;

use App\Enums\Chantiers\CostCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChantierCost extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    protected function casts(): array
    {
        return [
            'cost_date' => 'date',
            'category' => CostCategory::class,
            'amount' => 'decimal:2',
        ];
    }
}
