<?php

namespace App\Enums\Payroll;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PayrollDeductionType: string implements HasLabel
{
    case Breakage = 'breakage';
    case Insurance = 'insurance';
    case Advance = 'advance';
    case Other = 'other';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Breakage => __('payroll.deduction_type.breakage'),
            self::Insurance => __('payroll.deduction_type.insurance'),
            self::Advance => __('payroll.deduction_type.advance'),
            self::Other => __('payroll.deduction_type.other'),
        };
    }
}
