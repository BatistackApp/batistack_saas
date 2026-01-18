<?php

namespace App\Enums\Articles;

use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum StockMouvementType: string implements HasIcon, HasLabel
{
    case Entree = 'entree';
    case Sortie = 'sortie';
    case Ajustement = 'ajustement';
    case Transfert = 'transfert';
    case Production = 'production';
    case Consommation = 'consommation';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Entree => Heroicon::ArrowUp,
            self::Sortie => Heroicon::ArrowDown,
            self::Ajustement => Heroicon::AdjustmentsVertical,
            self::Transfert => Heroicon::ArrowsRightLeft,
            self::Production => Heroicon::ArchiveBox,
            self::Consommation => Heroicon::ShoppingCart,
            default => null,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Entree => 'Entrée',
            self::Sortie => 'Sortie',
            self::Ajustement => 'Ajustement',
            self::Transfert => 'Transfert',
            self::Production => 'Production',
            self::Consommation => 'Consommation',
            default => null,
        };
    }
}
