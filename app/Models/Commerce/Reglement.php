<?php

namespace App\Models\Commerce;

use App\Enums\Commerce\TypePaiement;
use App\Models\Core\Tenant;
use App\Observers\Commerce\ReglementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ReglementObserver::class])]
class Reglement extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    protected function casts(): array
    {
        return [
            'date_paiement' => 'date',
            'montant' => 'decimal:2',
            'type_paiement' => TypePaiement::class,
        ];
    }
}
