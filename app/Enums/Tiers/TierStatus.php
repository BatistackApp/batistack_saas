<?php

namespace App\Enums\Tiers;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TierStatus: string implements HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case Archived = 'archived';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Active => __('tiers.tier_status.active'),
            self::Inactive => __('tiers.tier_status.inactive'),
            self::Suspended => __('tiers.tier_status.suspended'),
            self::Archived => __('tiers.tier_status.archived'),
        };
    }
}
