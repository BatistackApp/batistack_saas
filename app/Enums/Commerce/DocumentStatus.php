<?php

namespace App\Enums\Commerce;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Validated = 'validated';
    case Accepted = 'accepted';
    case Invoiced = 'invoiced';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Validated => 'blue',
            self::Accepted => 'teal',
            self::Invoiced => 'indigo',
            self::PartiallyPaid => 'yellow',
            self::Paid => 'green',
            self::Cancelled => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-alt',
            self::Validated => 'heroicon-o-check-circle',
            self::Accepted => 'heroicon-o-hand-thumbs-up',
            self::Invoiced => 'heroicon-o-document-text',
            self::PartiallyPaid => 'heroicon-o-currency-dollar',
            self::Paid => 'heroicon-o-cash',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('commerce.status.draft'),
            self::Validated => __('commerce.status.validated'),
            self::Accepted => __('commerce.status.accepted'),
            self::Invoiced => __('commerce.status.invoiced'),
            self::PartiallyPaid => __('commerce.status.partially_paid'),
            self::Paid => __('commerce.status.paid'),
            self::Cancelled => __('commerce.status.cancelled'),
        };
    }
}
