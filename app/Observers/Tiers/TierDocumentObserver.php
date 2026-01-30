<?php

namespace App\Observers\Tiers;

use App\Enums\Tiers\TierDocumentType;
use App\Jobs\Tiers\VerifyUrssafAttestationJob;
use App\Models\Tiers\TierDocument;

class TierDocumentObserver
{
    public function created(TierDocument $tierDocument): void
    {
        $this->checkUrssaf($tierDocument);
    }

    public function updated(TierDocument $tierDocument): void
    {
        if ($tierDocument->wasChanged('verification_key')) $this->checkUrssaf($tierDocument);
    }

    private function checkUrssaf(TierDocument $doc): void {
        if ($doc->type === TierDocumentType::URSSAF->value && $doc->verification_key) {
            VerifyUrssafAttestationJob::dispatch($doc);
        }
    }
}
