<?php

namespace App\Models\Articles;

use App\Enums\Articles\ArticleType;
use App\Enums\Articles\UnitOfMeasure;
use App\Models\Core\Tenant;
use App\Observers\Articles\ArticleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ArticleObserver::class])]
class Article extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMouvement::class);
    }

    public function ouvrages(): HasMany
    {
        return $this->hasMany(Ouvrage::class);
    }

    public function ouvrageItems(): HasMany
    {
        return $this->hasMany(OuvrageItem::class);
    }

    protected function casts(): array
    {
        return [
            'type' => ArticleType::class,
            'unit_of_measure' => UnitOfMeasure::class,
            'weight_kg' => 'decimal:3',
            'volume_m3' => 'decimal:4',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'margin_percentage' => 'decimal:2',
            'is_active' => 'boolean',
            'requires_lot_tracking' => 'boolean',
            'requires_serial_number' => 'boolean',
        ];
    }
}
