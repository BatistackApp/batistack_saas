<?php

namespace App\Enums\Chantiers;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum CostCategory: string implements HasLabel
{
    case Labor = 'labor';
    case Materials = 'materials';
    case Rentals = 'rentals';
    case Subcontracting = 'subcontracting';
    case Other = 'other';


    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Labor => 'Travail',
            self::Materials => 'Matériels',
            self::Rentals => 'Locations',
            self::Subcontracting => 'Sous-traitance',
            self::Other => 'Autres',
        };
    }
}
