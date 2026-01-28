<?php

namespace App\Enums\Core;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum BillingCycle: string implements HasLabel
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case Quarterly = 'quarterly';

    public function getLabel(): string|Htmlable|null
    {
        return match($this) {
            self::Monthly => __('core.billing_cycle.monthly'),
            self::Yearly => __('core.billing_cycle.yearly'),
            self::Quarterly => __('core.billing_cycle.quarterly'),
        };
    }

    public function getDays(): int
    {
        return match($this) {
            self::Monthly => 30,
            self::Quarterly => 90,
            self::Yearly => 365,
        };
    }
}
