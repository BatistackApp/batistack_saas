<?php

namespace App\Models\Articles;

use App\Enums\Articles\ArticleUnit;
use App\Models\Core\Tenants;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'unit' => ArticleUnit::class,
            'cump_ht' => 'decimal:2',
            'purchase_price_ht' => 'decimal:2',
            'sale_price_ht' => 'decimal:2',
            'min_stock' => 'decimal:3',
            'alert_stock' => 'decimal:3',
        ];
    }

    public function tenants(): BelongsTo
    {
        return $this->belongsTo(Tenants::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Tiers::class, 'default_supplier_id');
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'article_warehouse')
            ->withPivot('quantity', 'bin_location')
            ->withTimestamps();
    }

    /**
     * Calcul du stock total (somme de tous les dépôts)
     */
    public function getTotalStockAttribute(): float {
        return (float) $this->warehouses()->sum('quantity');
    }
}
