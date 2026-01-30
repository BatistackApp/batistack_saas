<?php

namespace App\Models\Tiers;

use App\Enums\Tiers\TierPaymentTerm;
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
            'condition_reglement' => TierPaymentTerm::class,
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

    public function contacts(): HasMany
    {
        return $this->hasMany(TierContact::class, 'tiers_id');
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

    /**
     * Logique de conformité avancée (Vigilance segmentée)
     */
    public function getComplianceStatus(): string
    {
        // 1. Récupérer les types de ce tiers
        $tierTypes = $this->types()->pluck('type')->toArray();

        // 2. Vérifier les documents obligatoires manquants
        $mandatoryTypes = TierDocumentRequirement::whereIn('tier_type', $tierTypes)
            ->where('is_mandatory', true)
            ->pluck('document_type')
            ->toArray();

        $presentTypes = $this->documents()->pluck('type')->toArray();
        $missingMandatory = array_diff($mandatoryTypes, $presentTypes);

        if (! empty($missingMandatory)) {
            return 'non_conforme_manquant';
        }

        // 3. Vérifier les expirations
        $hasExpired = $this->documents()->where('status', 'expired')->exists();
        if ($hasExpired) {
            return 'non_conforme_expire';
        }

        $hasToRenew = $this->documents()->where('status', 'to_renew')->exists();
        if ($hasToRenew) {
            return 'a_renouveler';
        }

        return 'conforme';
    }

    public function isCompliant(): bool
    {
        return $this->getComplianceStatus() === 'conforme' || $this->getComplianceStatus() === 'a_renouveler';
    }
}
