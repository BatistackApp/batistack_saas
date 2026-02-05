<?php

namespace App\Enums\HR;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AbsenceType: string implements HasLabel
{
    case PaidLeave = 'paid_leave';
    case SickLeave = 'sick_leave';
    case UnpaidLeave = 'unpaid_leave';
    case Weather = 'weather';
    case Training = 'training';
    case Accident = 'accident';
    case Cut = 'cut';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PaidLeave => __('hr.absence_type.paid_leave'),
            self::SickLeave => __('hr.absence_type.sick_leave'),
            self::UnpaidLeave => __('hr.absence_type.unpaid_leave'),
            self::Weather => __('hr.absence_type.weather'),
            self::Training => __('hr.absence_type.training'),
            self::Accident => __('hr.absence_type.accident'),
            self::Cut => __('hr.absence_type.cut'),
        };
    }
}
