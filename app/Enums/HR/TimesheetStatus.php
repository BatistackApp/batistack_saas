<?php

namespace App\Enums\HR;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum TimesheetStatus: string implements HasLabel, HasIcon, HasColor
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Validated = 'validated';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::Validated => 'green',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => Heroicon::OutlinedPencil,
            self::Submitted, self::Validated => Heroicon::OutlinedCheck,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('hr.timesheet_status.draft'),
            self::Submitted => __('hr.timesheet_status.submitted'),
            self::Validated => __('hr.timesheet_status.validated'),
        };
    }
}
