<?php

namespace App\Models\Tiers;

use App\Enums\Tiers\TierDocumentStatus;
use App\Enums\Tiers\TierDocumentType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TierDocument extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => TierDocumentStatus::class,
            'expires_at' => 'date',
            'verified_at' => 'date',
            'montant_garantie' => 'decimal:2',
        ];
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tiers::class);
    }

    /**
     * Génère le lien de vérification URSSAF pré-rempli
     */
    protected function urssafVerificationUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->type !== TierDocumentType::URSSAF->value || ! $this->verification_key) {
                return null;
            }

            // Format : https://www.urssaf.fr/accueil/outils-documentation/outils/verification-attestation.html
            // Note: En pratique, on redirige vers l'outil avec les paramètres si l'URSSAF le permet par GET,
            // sinon on affiche les infos à copier-coller dans l'UI.
            return 'https://www.urssaf.fr/accueil/outils-documentation/outils/verification-attestation.html';
        });
    }
}
