<?php

namespace App\Enums\Articles;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StockMouvementReason: string implements HasLabel
{
    case Achat = 'achat';
    case Vente = 'vente';
    case Production = 'production';
    case Intervention = 'intervention';
    case AjustementManuel = 'ajustement_manuel';
    case Transfert = 'transfert';
    case Perte = 'perte';
    case Inventaire = 'inventaire';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Achat => 'Achat',
            self::Vente => 'Vente',
            self::Production => 'Production',
            self::Intervention => 'Intervention',
            self::AjustementManuel => 'Ajustement manuel',
            self::Transfert => 'Transfert',
            self::Perte => 'Perte',
            self::Inventaire => 'Inventaire',
            default => null,
        };
    }
}
