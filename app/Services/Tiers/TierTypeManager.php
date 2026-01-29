<?php

namespace App\Services\Tiers;

use App\Enums\Tiers\TierType;
use App\Models\Tiers\Tiers;

class TierTypeManager
{
    public function hasType(Tiers $tier, TierType $type): bool
    {
        return $tier->types()->where('type', $type->value)->exists();
    }
    public function addType(Tiers $tier, TierType $type, bool $isPrimary = false): void
    {
        if ($this->hasType($tier, $type)) {
            return;
        }

        if ($isPrimary) {
            $tier->types()->where('is_primary', true)->update(['is_primary' => false]);
        }

        $tier->types()->create([
            'type' => $type->value,
            'is_primary' => $isPrimary,
        ]);
    }

    public function removeType(Tiers $tier, TierType $type): void
    {
        $tier->types()->where('type', $type->value)->delete();
    }

    public function setPrimaryType(Tiers $tier, TierType $type): void
    {
        if (! $this->hasType($tier, $type)) {
            throw new \Exception("Tier does not have type: {$type->value}");
        }

        $tier->types()->update(['is_primary' => false]);
        $tier->types()->where('type', $type->value)->update(['is_primary' => true]);
    }

    public function getPrimaryType(Tiers $tier): ?TierType
    {
        $primaryType = $tier->types()->where('is_primary', true)->first();

        return $primaryType ? TierType::tryFrom($primaryType->type) : null;
    }
}
