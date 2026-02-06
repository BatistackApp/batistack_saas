<?php

namespace App\Enums\Payroll;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PayrollStatus: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Validated = 'validated';
    case Paid = 'paid';

    public function getColor(): string|array|null
    {
        return match($this) {
            self::Draft => 'gray',
            self::Validated => 'blue',
            self::Paid => 'green',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match($this) {
            self::Draft => 'heroicon-o-pencil',
            self::Validated => 'heroicon-o-check',
            self::Paid => 'heroicon-o-currency-dollar',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match($this) {
            self::Draft => __('payroll.statuses.draft'),
            self::Validated => __('payroll.statuses.validated'),
            self::Paid => __('payroll.statuses.paid'),
        };
    }
}
