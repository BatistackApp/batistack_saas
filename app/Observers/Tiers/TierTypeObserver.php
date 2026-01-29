<?php

namespace App\Observers\Tiers;

use App\Models\Tiers\TierType;

class TierTypeObserver
{
    public function creating(TierType $tierType): void
    {
        if ($tierType->is_primary === false && ! $tierType->tiers->types()->exists()) {
            $tierType->is_primary = true;
        }
    }

    public function created(TierType $tierType): void
    {
        // Retirer la primauté des autres si celui-ci est marqué primaire
        if ($tierType->is_primary) {
            $tierType->tiers
                ->types()
                ->where('id', '!=', $tierType->id)
                ->update(['is_primary' => false]);
        }
    }
}
