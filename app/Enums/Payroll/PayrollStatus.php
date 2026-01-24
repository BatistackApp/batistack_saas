<?php

namespace App\Enums\Payroll;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum PayrollStatus: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Validated = 'validated';
    case Exported = 'exported';
    case Archived = 'archived';


    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Validated => 'green',
            self::Exported => 'blue',
            self::Archived => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => Heroicon::OutlinedPencil,
            self::Validated => Heroicon::OutlinedCheck,
            self::Exported => Heroicon::OutlinedArrowDownCircle,
            self::Archived => Heroicon::OutlinedArchiveBox,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('payroll.status.draft'),
            self::Validated => __('payroll.status.validated'),
            self::Exported => __('payroll.status.exported'),
            self::Archived => __('payroll.status.archived'),
        };
    }
}
