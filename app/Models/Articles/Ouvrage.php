<?php

namespace App\Models\Articles;

use App\Enums\Articles\ArticleUnit;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ouvrage extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'unit' => ArticleUnit::class,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenants::class);
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
     * Coût de revient théorique de l'ouvrage.
     * Basé sur le Coût Unitaire Moyen Pondéré (CUMP) actuel des articles composants.
     */
    public function getTheoreticalCostAttribute(): float {
        return (float) $this->components->sum(function($article) {
            return (float) $article->pivot->quantity_needed * (float) $article->cump_ht;
        });
    }
}
