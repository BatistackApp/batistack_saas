<?php

namespace App\Models\Tiers;

use App\Enums\Tiers\TierDocumentStatus;
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
     * Logique de conformité augmentée (Recommandations 2 & 3)
     */
    public function getComplianceStatus(): string
    {
        $tierTypes = $this->types()->pluck('type')->toArray();

        // 1. Vérification des documents obligatoires manquants
        $mandatoryTypes = TierDocumentRequirement::whereIn('tier_type', $tierTypes)
            ->where('is_mandatory', true)
            ->pluck('document_type')
            ->toArray();

        $presentTypes = $this->documents()->pluck('type')->toArray();
        $missingMandatory = array_diff($mandatoryTypes, $presentTypes);

        if (! empty($missingMandatory)) {
            return 'non_conforme_manquant';
        }

        // 2. Vérification de la validation humaine (Pending Verification)
        $hasPending = $this->documents()
            ->whereIn('type', $mandatoryTypes)
            ->where('status', TierDocumentStatus::Pending_verification)
            ->exists();

        if ($hasPending) {
            return 'en_attente_verification';
        }

        // 3. Vérification des expirations
        $hasExpired = $this->documents()->where('status', TierDocumentStatus::Expired)->exists();
        if ($hasExpired) {
            return 'non_conforme_expire';
        }

        // 4. Intégration des qualifications à la conformité (Recommandation 3)
        $hasExpiredQualif = $this->qualifications()
            ->where('valid_until', '<', now())
            ->exists();
        if ($hasExpiredQualif) {
            return 'qualification_expiree';
        }

        $hasToRenew = $this->documents()->where('status', TierDocumentStatus::ToRenew)->exists();
        if ($hasToRenew) {
            return 'a_renouveler';
        }

        return 'conforme';
    }

    public function isCompliant(): bool
    {
        return in_array($this->getComplianceStatus(), ['conforme', 'a_renouveler']);
    }
}
