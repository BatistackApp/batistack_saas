<?php

namespace App\Enums\Articles;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum InventorySessionStatus: string implements HasColor, HasLabel
{
    case Open = 'open';           // Initialisé, récupération du théorique
    case Counting = 'counting';   // Saisie des comptages en cours
    case Closed = 'closed';       // Saisie terminée, en attente de validation
    case Validated = 'validated'; // Terminé, ajustements passés en stock
    case Cancelled = 'cancelled';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Open => 'blue',
            self::Counting => 'purple',
            self::Closed, self::Cancelled => 'red',
            self::Validated => 'green',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Open => __('articles.inventory_status.open'),
            self::Counting => __('articles.inventory_status.counting'),
            self::Closed => __('articles.inventory_status.closed'),
            self::Validated => __('articles.inventory_status.validated'),
            self::Cancelled => __('articles.inventory_status.cancelled'),
        };
    }
}
