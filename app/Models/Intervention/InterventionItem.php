<?php

namespace App\Models\Intervention;

use App\Models\Articles\Article;
use App\Models\Articles\ArticleSerialNumber;
use App\Models\Articles\Ouvrage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterventionItem extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function ouvrage(): BelongsTo
    {
        return $this->belongsTo(Ouvrage::class);
    }

    public function serialNumber(): BelongsTo
    {
        return $this->belongsTo(ArticleSerialNumber::class, 'article_serial_number_id');
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price_ht' => 'decimal:2',
            'unit_cost_ht' => 'decimal:2',
            'total_ht' => 'decimal:2',
            'is_billable' => 'boolean',
        ];
    }
}
