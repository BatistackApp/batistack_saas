<?php

namespace App\Enums\Commerce;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum QuoteStatus: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Lost = 'lost';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'blue',
            self::Accepted => 'green',
            self::Rejected => 'yellow',
            self::Lost => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-alt',
            self::Sent => 'heroicon-o-paper-airplane',
            self::Accepted => 'heroicon-o-check-circle',
            self::Rejected => 'heroicon-o-x-circle',
            self::Lost => 'heroicon-o-exclamation-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('commerce.quote.statuses.draft'),
            self::Sent => __('commerce.quote.statuses.sent'),
            self::Accepted => __('commerce.quote.statuses.accepted'),
            self::Rejected => __('commerce.quote.statuses.rejected'),
            self::Lost => __('commerce.quote.statuses.lost'),
        };
    }
}
