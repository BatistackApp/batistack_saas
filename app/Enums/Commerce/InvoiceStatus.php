<?php

namespace App\Enums\Commerce;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum InvoiceStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Validated = 'validated';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overdue = 'overdue';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Validated => 'blue',
            self::PartiallyPaid => 'yellow',
            self::Paid => 'green',
            self::Overdue => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-document-text',
            self::Validated => 'heroicon-o-check-circle',
            self::PartiallyPaid => 'heroicon-o-clock',
            self::Paid => 'heroicon-o-currency-dollar',
            self::Overdue => 'heroicon-o-exclamation-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('commerce.invoice.statuses.draft'),
            self::Validated => __('commerce.invoice.statuses.validated'),
            self::PartiallyPaid => __('commerce.invoice.statuses.partially_paid'),
            self::Paid => __('commerce.invoice.statuses.paid'),
            self::Overdue => __('commerce.invoice.statuses.overdue'),
        };
    }
}
