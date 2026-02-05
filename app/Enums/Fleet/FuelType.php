<?php

namespace App\Enums\Fleet;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum FuelType: string implements HasLabel
{
    case Diesel = 'diesel';
    case GNR = 'gnr';
    case Electric = 'electric';
    case Petrol = 'petrol';
    case Hybrid = 'hybrid';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Diesel => __('fleet.fuel_types.diesel'),
            self::GNR => __('fleet.fuel_types.gnr'),
            self::Electric => __('fleet.fuel_types.electric'),
            self::Petrol => __('fleet.fuel_types.petrol'),
            self::Hybrid => __('fleet.fuel_types.hybrid'),
        };
    }
}
