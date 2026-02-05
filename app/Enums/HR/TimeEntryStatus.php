<?php

namespace App\Enums\HR;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TimeEntryStatus: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';


    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil',
            self::Submitted => 'heroicon-o-paper-airplane',
            self::Approved => 'heroicon-o-check',
            self::Rejected => 'heroicon-o-x',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('hr.time_entry_status.draft'),
            self::Submitted => __('hr.time_entry_status.submitted'),
            self::Approved => __('hr.time_entry_status.approved'),
            self::Rejected => __('hr.time_entry_status.rejected'),
        };
    }
}
