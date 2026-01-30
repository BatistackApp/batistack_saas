<?php

namespace App\Services\Tiers;

use App\Enums\Tiers\TierComplianceStatus;
use App\Enums\Tiers\TierDocumentStatus;
use App\Models\Tiers\TierDocument;
use App\Models\Tiers\TierDocumentRequirement;
use App\Models\Tiers\Tiers;

class TierComplianceService
{
    /**
     * Calcule le statut de conformité globale d'un tiers.
     */
    public function getComplianceStatus(Tiers $tier): string
    {
        $tierTypes = $tier->types()->pluck('type')->toArray();

        // 1. Vérification des documents obligatoires manquants
        $mandatoryTypes = TierDocumentRequirement::whereIn('tier_type', $tierTypes)
            ->where('is_mandatory', true)
            ->pluck('document_type')
            ->toArray();

        $presentTypes = $tier->documents()->pluck('type')->toArray();
        $missingMandatory = array_diff($mandatoryTypes, $presentTypes);

        if (! empty($missingMandatory)) {
            return TierComplianceStatus::NonCompliantMissing->value;
        }

        // 2. Vérification de la validation humaine
        $hasPending = $tier->documents()
            ->whereIn('type', $mandatoryTypes)
            ->where('status', TierDocumentStatus::Pending_verification->value)
            ->exists();

        if ($hasPending) {
            return TierComplianceStatus::PendingVerification->value;
        }

        // 3. Vérification des expirations
        $hasExpired = $tier->documents()->where('status', TierDocumentStatus::Expired->value)->exists();
        if ($hasExpired) {
            return TierComplianceStatus::NonCompliantExpired->value;
        }

        // 4. Vérification des qualifications
        $hasExpiredQualif = $tier->qualifications()
            ->where('valid_until', '<', now())
            ->exists();
        if ($hasExpiredQualif) {
            return TierComplianceStatus::QualificationExpired->value;
        }

        $hasToRenew = $tier->documents()->where('status', TierDocumentStatus::ToRenew->value)->exists();
        if ($hasToRenew) {
            return TierComplianceStatus::ToRenew->value;
        }

        return TierComplianceStatus::Compliant->value;
    }

    public function auditCompliance(Tiers $tier): void
    {
        $tier->documents->each(function (TierDocument $document) {
            if ($document->expires_at->isPast()) {
                $document->update(['status' => TierDocumentStatus::Expired]);
            } elseif ($document->expires_at->diffInDays(now()) < 30) {
                $document->update(['status' => TierDocumentStatus::ToRenew]);
            } else {
                $document->update(['status' => TierDocumentStatus::Valid]);
            }
        });
    }
}
