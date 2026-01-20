<?php

namespace App\Enums\Articles;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ArticleType: string implements HasLabel
{
    case Produit = 'produit';
    case Service = 'service';
    case MatierePremiere = 'matiere_premiere';
    case SousAssemblage = 'sous_assemblage';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Produit => 'Produit',
            self::Service => 'Service',
            self::MatierePremiere => 'Matière première',
            self::SousAssemblage => 'Sous-assemblage',
            default => null,
        };
    }
}
