<?php

namespace App\Enums\Fleet;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum InspectionType: string implements HasLabel
{
    case CT = 'controle_technique';
    case VGP = 'vgp';
    case Tachograph = 'chronotachygraphe';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::CT => __('fleet.inspection_types.ct'),
            self::VGP => __('fleet.inspection_types.vgp'),
            self::Tachograph => __('fleet.inspection_types.tachograph'),
        };
    }
}
