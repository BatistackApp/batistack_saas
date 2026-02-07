<?php

namespace App\Enums\Locations;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RentalStatus: string implements HasLabel, HasColor, HasIcon
{
    case DRAFT = 'draft';         // En préparation
    case ACTIVE = 'active';       // Matériel sur chantier
    case ENDED = 'ended';         // Matériel rendu, en attente de facture
    case INVOICED = 'invoiced';   // Facture fournisseur rapprochée
    case CANCELLED = 'cancelled'; // Annulé

    public function getColor(): string|array|null
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'green',
            self::ENDED => 'orange',
            self::INVOICED => 'blue',
            self::CANCELLED => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match($this) {
            self::DRAFT => 'heroicon-o-document-text',
            self::ACTIVE, self::INVOICED => 'heroicon-o-check',
            self::ENDED, self::CANCELLED => 'heroicon-o-x',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match($this) {
            self::DRAFT => __('rental.statuses.draft'),
            self::ACTIVE => __('rental.statuses.active'),
            self::ENDED => __('rental.statuses.ended'),
            self::INVOICED => __('rental.statuses.invoiced'),
            self::CANCELLED => __('rental.statuses.cancelled'),
        };
    }
}
