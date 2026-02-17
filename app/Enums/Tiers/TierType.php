<?php

namespace App\Enums\Tiers;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TierType: string implements HasColor, HasLabel
{
    case Customer = 'customer';
    case Supplier = 'supplier';
    case Subcontractor = 'subcontractor';
    case Employee = 'employee';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Customer => 'green',
            self::Supplier => 'blue',
            self::Subcontractor => 'amber',
            self::Employee => 'purple',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Customer => __('tiers.tier_type.customer'),
            self::Supplier => __('tiers.tier_type.supplier'),
            self::Subcontractor => __('tiers.tier_type.subcontractor'),
            self::Employee => __('tiers.tier_type.employee'),
        };
    }
}
