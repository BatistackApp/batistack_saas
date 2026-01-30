<?php

namespace App\Models\Tiers;

use App\Enums\Tiers\TierStatus;
use App\Models\Core\Tenants;
use App\Models\User;
use App\Observers\Tiers\TierObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

#[ObservedBy([TierObserver::class])]
class Tiers extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => TierStatus::class,
            'metadata' => 'array',
            'has_compte_prorata' => 'boolean',
            'retenue_garantie_pct' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenants::class);
    }

    public function types(): HasMany
    {
        return $this->hasMany(TierType::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TierDocument::class, 'tiers_id');
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(TierQualification::class, 'tiers_id');
    }

    // Accessors
    protected function displayName(): Attribute
    {
        return Attribute::get(
            fn () => $this->type_entite === 'personne_morale'
                ? mb_strtoupper($this->raison_social ?? '')
                : "{$this->prenom} ".mb_strtoupper($this->nom ?? '')
        );
    }

    public function isCompliant(): bool
    {
        // 1. Vérification des documents administratifs
        $hasInvalidDocs = $this->documents()
            ->whereIn('status', ['expired', 'missing'])
            ->exists();

        // 2. Vérification des qualifications techniques expirées
        $hasExpiredQualifs = $this->qualifications()
            ->where('valid_until', '<', now())
            ->exists();

        return ! $hasInvalidDocs && ! $hasExpiredQualifs;
    }
}
