<?php

namespace App\Enums\Accounting;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum EntryStatus: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Validated = 'validated';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft, self::Cancelled => 'gray',
            self::Validated => 'green',
            self::Closed => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-document-text',
            self::Validated => 'heroicon-o-check-circle',
            self::Closed => 'heroicon-o-x',
            self::Cancelled => 'heroicon-o-x',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match($this) {
            self::Draft => __('accounting.entry_status.draft'),
            self::Validated => __('accounting.entry_status.validated'),
            self::Closed => __('accounting.entry_status.closed'),
            self::Cancelled => __('accounting.entry_status.cancelled'),
        };
    }
}
