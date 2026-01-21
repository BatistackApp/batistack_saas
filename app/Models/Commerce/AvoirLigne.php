<?php

namespace App\Models\Commerce;

use App\Models\Articles\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvoirLigne extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function avoir(): BelongsTo
    {
        return $this->belongsTo(Avoir::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    protected function casts(): array
    {
        return [
            'quantite' => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
            'montant_ht' => 'decimal:2',
        ];
    }
}
