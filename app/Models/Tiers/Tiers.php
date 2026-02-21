<?php

namespace App\Models\Tiers;

use App\Enums\Tiers\TierComplianceStatus;
use App\Enums\Tiers\TierPaymentTerm;
use App\Enums\Tiers\TierStatus;
use App\Models\User;
use App\Observers\Tiers\TierObserver;
use App\Services\Tiers\TierComplianceService;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

#[ObservedBy([TierObserver::class])]
class Tiers extends Model
{
    use HasFactory, HasTenant, Notifiable, SoftDeletes;

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

    public function types(): HasMany
    {
        return $this->hasMany(TierType::class);
    }

    // Un tiers à un seul accès à User
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
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
        return app(TierComplianceService::class)->getComplianceStatus($this);
    }

    public function isCompliant(): bool
    {
        return in_array($this->getComplianceStatus(), [
            TierComplianceStatus::Compliant->value,
            TierComplianceStatus::ToRenew->value,
        ]);
    }
}
