<?php

namespace App\Enums\Payroll;

use Filament\Support\Contracts\HasLabel;

enum BtpTravelZone: string implements HasLabel
{
    case Zone1 = '1';
    case Zone2 = '2';
    case Zone3 = '3';
    case Zone4 = '4';
    case Zone5 = '5';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Zone1 => 'Zone 1 (0-10 km)',
            self::Zone2 => 'Zone 2 (10-20 km)',
            self::Zone3 => 'Zone 3 (20-30 km)',
            self::Zone4 => 'Zone 4 (30-40 km)',
            self::Zone5 => 'Zone 5 (40-50 km)',
        };
    }
}
