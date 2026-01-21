<?php

namespace App\Models\Commerce;

use App\Enums\Commerce\TaxRate;
use App\Models\Articles\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandeLigne extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    protected function casts(): array
    {
        return [
            'quantite_commandee' => 'decimal:2',
            'quantite_livree' => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
            'montant_ht' => 'decimal:2',
            'tva' => TaxRate::class,
        ];
    }
}
