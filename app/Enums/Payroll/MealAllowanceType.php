<?php

namespace App\Enums\Payroll;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum MealAllowanceType: string implements HasLabel
{
    case Forfeit = 'forfeit';
    case PerDay = 'per_day';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Forfeit => __('payroll.meal_allowance_type.forfeit'),
            self::PerDay => __('payroll.meal_allowance_type.per_day'),
        };
    }
}
