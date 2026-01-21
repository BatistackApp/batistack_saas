<?php

namespace App\Enums\Commerce;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum CommandeStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case PartiallyDelivered = 'partially_delivered';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Confirmed => 'blue',
            self::PartiallyDelivered => 'yellow',
            self::Delivered => 'green',
            self::Cancelled => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-alt',
            self::Confirmed => 'heroicon-o-check-circle',
            self::PartiallyDelivered => 'heroicon-o-truck',
            self::Delivered => 'heroicon-o-clipboard-check',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('commerce.commande_status.draft'),
            self::Confirmed => __('commerce.commande_status.confirmed'),
            self::PartiallyDelivered => __('commerce.commande_status.partially_delivered'),
            self::Delivered => __('commerce.commande_status.delivered'),
            self::Cancelled => __('commerce.commande_status.cancelled'),
        };
    }
}
