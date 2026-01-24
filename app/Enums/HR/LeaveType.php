<?php

namespace App\Enums\HR;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum LeaveType: string implements HasLabel
{
    case PaidLeave = 'paid_leave';
    case UnpaidLeave = 'unpaid_leave';
    case SickLeave = 'sick_leave';
    case Other = 'other';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PaidLeave => __('hr.leave_type.paid_leave'),
            self::UnpaidLeave => __('hr.leave_type.unpaid_leave'),
            self::SickLeave => __('hr.leave_type.sick_leave'),
            self::Other => __('hr.leave_type.other'),
        };
    }
}
