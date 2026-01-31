<?php

namespace App\Enums\Articles;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ArticleUnit: string implements HasLabel
{
    case Unit = 'u';     // Unité / Pièce
    case Meter = 'm';    // Mètre linéaire
    case SquareMeter = 'm2'; // Mètre carré
    case CubicMeter = 'm3';  // Mètre cube
    case Kilogram = 'kg';    // Kilogramme
    case Ton = 't';      // Tonne
    case Liter = 'l';    // Litre
    case Pack = 'pack';  // Forfait / Lot

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Unit => __('articles.unit.u'),
            self::Meter => __('articles.unit.m'),
            self::SquareMeter => __('articles.unit.m2'),
            self::CubicMeter => __('articles.unit.m3'),
            self::Kilogram => __('articles.unit.kg'),
            self::Ton => __('articles.unit.t'),
            self::Liter => __('articles.unit.l'),
            self::Pack => __('articles.unit.pack'),
            default => null,
        };
    }
}
