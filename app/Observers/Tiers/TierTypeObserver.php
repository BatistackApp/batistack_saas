<?php

namespace App\Observers\Tiers;

use App\Models\Tiers\TierType;

class TierTypeObserver
{
    public function creating(TierType $tierType): void
    {
        if ($tierType->is_primary === false && $tierType->tiers_id) {
            $hasPrimary = TierType::where('tiers_id', $tierType->tiers_id)
                ->where('is_primary', true)
                ->exists();

            if (!$hasPrimary) {
                $tierType->is_primary = true;
            }
        }
    }

    public function created(TierType $tierType): void
    {
        if ($tierType->is_primary) {
            $tierType->tier
                ->types()
                ->where('id', '!=', $tierType->id)
                ->update(['is_primary' => false]);
        }
    }
}
