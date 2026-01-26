<?php

namespace App\Enums\Payroll;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TravelAllowanceType: string implements HasLabel
{
    case Kilometre = 'kilometre';
    case Forfeit = 'forfeit';


    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Kilometre => __('payroll.travel_allowance.kilometre'),
            self::Forfeit => __('payroll.travel_allowance.forfeit'),
        };
    }
}
