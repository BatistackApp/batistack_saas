<?php

namespace App\Observers\Tiers;

use App\Models\Tiers\Tiers;
use App\Services\Tiers\TierCodeGenerator;

class TierObserver
{
    public function __construct(private TierCodeGenerator $codeGenerator) {}

    public function creating(Tiers $tier): void
    {
        if (empty($tier->code_tiers)) {
            $tier->code_tiers = $this->codeGenerator->generateWithRetry();
        }

        if (empty($tier->pays)) {
            $tier->pays = 'FR';
        }
    }

    public function created(Tiers $tier): void {}

    public function updating(Tiers $tier): void {}

    public function updated(Tiers $tier): void {}

    public function deleting(Tiers $tier): void {}
}
