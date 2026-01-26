<?php

namespace App\Enums\Payroll;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum OvertimeType: string implements HasLabel
{
    case Standard = 'standard';
    case Night = 'night';
    case Sunday = 'sunday';
    case Public = 'public';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Standard => __('payroll.overtime.standard'),
            self::Night => __('payroll.overtime.night'),
            self::Sunday => __('payroll.overtime.sunday'),
            self::Public => __('payroll.overtime.public'),
        };
    }
}
