<?php

namespace App\Enums\Tiers;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AddressType: string implements HasLabel
{
    case SiegeSocial = 'siege_social';
    case Facturation = 'facturation';
    case Livraison = 'livraison';
    case Autre = 'autre';


    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::SiegeSocial => 'Siege Social',
            self::Facturation => 'Facturation',
            self::Livraison => 'Livraison',
            self::Autre => 'Autre',
            default => null,
        };
    }
}
