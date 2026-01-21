<?php

namespace App\Models\Commerce;

use App\Models\Chantiers\Chantier;
use App\Models\Core\Tenant;
use App\Models\Tiers\Tiers;
use App\Observers\Commerce\FactureObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([FactureObserver::class])]
class Facture extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Tiers::class);
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }

    public function situation(): BelongsTo
    {
        return $this->belongsTo(Situation::class);
    }

    public function reglements(): HasMany
    {
        return $this->hasMany(Reglement::class);
    }

    public function avoirs(): HasMany
    {
        return $this->hasMany(Avoir::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(FactureLigne::class);
    }

    protected function casts(): array
    {
        return [
            'date_facture' => 'date',
            'date_echeance' => 'date',
            'montant_ht' => 'decimal:2',
            'montant_tva' => 'decimal:2',
            'montant_ttc' => 'decimal:2',
            'montant_paye' => 'decimal:2',
        ];
    }
}
