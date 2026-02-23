<?php

namespace App\Models\Expense;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseMileageScale extends Model
{
    use HasFactory, HasTenant;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'active_year' => 'integer',
            'vehicle_power' => 'integer',
            'min_km' => 'integer',
            'max_km' => 'integer',
            'rate_per_km' => 'decimal:4',
            'fixed_amount' => 'decimal:2',
        ];
    }

    /**
     * Scope pour trouver le barème applicable selon la distance cumulée.
     */
    public function scopeForDistance($query, int $power, float $totalDistanceYear)
    {
        return $query->where('vehicle_power', $power)
            ->where('min_km', '<=', $totalDistanceYear)
            ->where(function ($q) use ($totalDistanceYear) {
                $q->where('max_km', '>=', $totalDistanceYear)
                    ->orWhereNull('max_km');
            });
    }
}
