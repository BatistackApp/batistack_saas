<?php

namespace App\Enums\Fleet;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum VehicleType: string implements HasLabel
{
    case Van = 'van';
    case Truck = 'truck';
    case Excavator = 'excavator';
    case Loader = 'loader';
    case Crane = 'crane';
    case Car = 'car';
    case Other = 'other';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Van => __('fleet.vehicle_types.van'),
            self::Truck => __('fleet.vehicle_types.truck'),
            self::Excavator => __('fleet.vehicle_types.excavator'),
            self::Loader => __('fleet.vehicle_types.loader'),
            self::Crane => __('fleet.vehicle_types.crane'),
            self::Car => __('fleet.vehicle_types.car'),
            self::Other => __('fleet.vehicle_types.other'),
        };
    }
}
