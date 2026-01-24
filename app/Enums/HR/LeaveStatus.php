<?php

namespace App\Enums\HR;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum LeaveStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Pending => Heroicon::OutlinedClock,
            self::Approved => Heroicon::OutlinedCheck,
            self::Rejected => Heroicon::OutlinedXCircle,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Pending => __('hr.leave_status.pending'),
            self::Approved => __('hr.leave_status.approved'),
            self::Rejected => __('hr.leave_status.rejected'),
        };
    }
}
