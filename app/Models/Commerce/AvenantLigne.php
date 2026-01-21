<?php

namespace App\Models\Commerce;

use App\Models\Articles\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvenantLigne extends Model
{
    use HasFactory;

    public function avenant(): BelongsTo
    {
        return $this->belongsTo(Avenant::class);
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
