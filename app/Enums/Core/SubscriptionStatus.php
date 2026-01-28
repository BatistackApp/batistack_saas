<?php

namespace App\Enums\Core;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use BackedEnum;

enum SubscriptionStatus: string implements HasLabel, HasColor, HasIcon
{
    case Active = 'active';
    case Paused = 'paused';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function getColor(): string|array|null
    {
        return match($this) {
            self::Active => 'success',
            self::Paused => 'warning',
            self::PastDue => 'danger',
            self::Cancelled => 'gray',
            self::Expired => 'gray',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match($this) {
            self::Active => Heroicon::CheckCircle,
            self::Paused => Heroicon::PauseCircle,
            self::PastDue => Heroicon::ExclamationTriangle,
            self::Cancelled => Heroicon::XCircle,
            self::Expired => Heroicon::ArchiveBoxXMark,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match($this) {
            self::Active => __('core.subscription_status.active'),
            self::Paused => __('core.subscription_status.paused'),
            self::PastDue => __('core.subscription_status.past_due'),
            self::Cancelled => __('core.subscription_status.cancelled'),
            self::Expired => __('core.subscription_status.expired'),
        };
    }
}
