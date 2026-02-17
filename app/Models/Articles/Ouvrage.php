<?php

namespace App\Models\Articles;

use App\Enums\Articles\ArticleUnit;
use App\Observers\Articles\OuvrageObserver;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([OuvrageObserver::class])]
class Ouvrage extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'unit' => ArticleUnit::class,
        ];
    }

    /**
     * Nomenclature : Liste des articles (matériaux) composant l'ouvrage.
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'ouvrage_article')
            ->withPivot('quantity_needed')
            ->withTimestamps();
    }

    /**
     * COÛT THÉORIQUE "PRUDENT"
     * Calcule le coût en incluant les pertes et chutes définies dans la nomenclature.
     */
    public function getTheoreticalCostAttribute(): float
    {
        return (float) $this->components->sum(function ($article) {
            $qty = (float) $article->pivot->quantity_needed;
            $wastage = (float) ($article->pivot->wastage_factor_pct ?? 0);

            // On valorise la quantité qui sera réellement déstockée
            $realQty = $qty * (1 + ($wastage / 100));

            return $realQty * (float) $article->cump_ht;
        });
    }
}
