<?php

namespace App\Enums\Articles;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StockMovementType: string implements HasLabel, HasColor
{
    case Entry = 'entry';       // Réception fournisseur
    case Exit = 'exit';         // Consommation chantier
    case Transfer = 'transfer'; // Transfert inter-dépôts
    case Adjustment = 'adj';    // Correction d'inventaire / Perte

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Entry => 'green',
            self::Exit => 'red',
            self::Transfer => 'amber',
            self::Adjustment => 'blue',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Entry => __('articles.movement.entry'),
            self::Exit => __('articles.movement.exit'),
            self::Transfer => __('articles.movement.transfer'),
            self::Adjustment => __('articles.movement.adj'),
        };
    }
}
