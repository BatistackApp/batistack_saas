<?php

namespace App\Enums\Tiers;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TierType: string implements HasLabel
{
    case Client = 'client';
    case Fournisseur = 'fournisseur';
    case SousTrustee = 'sous_traitant';


    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Client => 'Client',
            self::Fournisseur => 'Fournisseur',
            self::SousTrustee => 'Sous-trusteur',
            default => null,
        };
    }
}
