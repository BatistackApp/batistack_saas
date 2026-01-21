<?php

namespace App\Models\Commerce;

use App\Enums\Commerce\CommandeStatus;
use App\Models\Chantiers\Chantier;
use App\Models\Core\Tenant;
use App\Models\Tiers\Tiers;
use App\Observers\Commerce\CommandeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([CommandeObserver::class])]
class Commande extends Model
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

    public function lignes(): HasMany
    {
        return $this->hasMany(CommandeLigne::class);
    }

    public function avenants(): HasMany
    {
        return $this->hasMany(Avenant::class);
    }

    protected function casts(): array
    {
        return [
            'date_commande' => 'date',
            'montant_ht' => 'decimal:2',
            'montant_tva' => 'decimal:2',
            'montant_ttc' => 'decimal:2',
            'status' => CommandeStatus::class,
        ];
    }
}
