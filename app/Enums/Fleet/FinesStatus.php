<?php

namespace App\Enums\Fleet;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum FinesStatus: string implements HasColor, HasIcon, HasLabel
{
    case Received = 'received';             // Reçu (saisie initiale)
    case DriverAssigned = 'driver_assigned'; // Conducteur identifié/désigné en interne
    case Contested = 'contested';           // Contestation en cours
    case Paid = 'paid';                     // Payé par l'entreprise (ex: stationnement)
    case Archived = 'archived';             // Dossier clos

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Received => 'red',
            self::DriverAssigned => 'amber',
            self::Contested => 'blue',
            self::Paid => 'green',
            self::Archived => 'gray',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Received => 'heroicon-o-document',
            self::DriverAssigned => 'heroicon-o-user',
            self::Contested => 'heroicon-o-shield-exclamation',
            self::Paid => 'heroicon-o-currency-euro',
            self::Archived => 'heroicon-o-archive',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Received => __('fleet.fines_statuses.received'),
            self::DriverAssigned => __('fleet.fines_statuses.driver_assigned'),
            self::Contested => __('fleet.fines_statuses.contested'),
            self::Paid => __('fleet.fines_statuses.paid'),
            self::Archived => __('fleet.fines_statuses.archived'),
        };
    }
}
