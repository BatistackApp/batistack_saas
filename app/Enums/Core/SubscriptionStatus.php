<?php

namespace App\Enums\Core;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum SubscriptionStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Cancelled = 'cancelled';
    case PastDue = 'past_due';
    case Trialing = 'trialing';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'bg-green-500',
            self::Inactive => 'bg-gray-500',
            self::Cancelled => 'bg-red-500',
            self::PastDue => 'bg-yellow-500',
            self::Trialing => 'bg-yellow-500',
            default => null,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Cancelled => 'Cancelled',
            self::PastDue => 'Past Due',
            self::Trialing => 'Trialing',
        };
    }
}
