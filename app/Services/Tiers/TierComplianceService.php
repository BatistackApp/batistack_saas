<?php

namespace App\Services\Tiers;

use App\Enums\Tiers\TierDocumentStatus;
use App\Models\Tiers\TierDocument;
use App\Models\Tiers\Tiers;

class TierComplianceService
{
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
