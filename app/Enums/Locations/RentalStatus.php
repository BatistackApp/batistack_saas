<?php

namespace App\Enums\Locations;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RentalStatus: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 'draft';         // En préparation
    case ACTIVE = 'active';       // Matériel sur chantier
    case OFF_HIRE = 'off_hire';         // Appel de reprise effectué, en attente de récupération
    case ENDED = 'ended';         // Matériel rendu, en attente de facture
    case INVOICED = 'invoiced';   // Facture fournisseur rapprochée
    case CANCELLED = 'cancelled'; // Annulé

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'green',
            self::ENDED, self::OFF_HIRE => 'orange',
            self::INVOICED => 'blue',
            self::CANCELLED => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-document-text',
            self::ACTIVE, self::INVOICED => 'heroicon-o-check',
            self::ENDED, self::CANCELLED => 'heroicon-o-x',
            self::OFF_HIRE => 'heroicon-o-phone-arrow-up-right',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => __('locations.statuses.draft'),
            self::ACTIVE => __('locations.statuses.active'),
            self::ENDED => __('locations.statuses.ended'),
            self::OFF_HIRE => __('locations.statuses.off_hire'),
            self::INVOICED => __('locations.statuses.invoiced'),
            self::CANCELLED => __('locations.statuses.cancelled'),
        };
    }
}
