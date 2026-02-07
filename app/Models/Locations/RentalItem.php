<?php

namespace App\Models\Locations;

use App\Models\Articles\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'rental_contract_id', 'article_id', 'label', 'quantity',
        'daily_rate_ht', 'weekly_rate_ht', 'monthly_rate_ht',
        'is_weekend_included', 'insurance_pct'
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(RentalContract::class, 'rental_contract_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    protected function casts(): array
    {
        return [
            'is_weekend_included' => 'boolean',
            'quantity' => 'decimal:2',
            'daily_rate_ht' => 'decimal:2',
            'weekly_rate_ht' => 'decimal:2',
            'monthly_rate_ht' => 'decimal:2',
            'insurance_pct' => 'float',
        ];
    }
}
