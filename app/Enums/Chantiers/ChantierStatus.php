<?php

namespace App\Enums\Chantiers;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum ChantierStatus: string implements HasColor, HasIcon, HasLabel
{
    case Planned = 'planned';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Planned => 'blue',
            self::Active => 'green',
            self::Paused => 'yellow',
            self::Completed => 'gray',
            self::Cancelled => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Planned => Heroicon::Calendar,
            self::Active => Heroicon::CheckCircle,
            self::Paused => Heroicon::PauseCircle,
            self::Completed => Heroicon::OutlinedFlag,
            self::Cancelled => Heroicon::OutlinedXCircle,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Planned => 'Plannifié',
            self::Active => 'Actif',
            self::Paused => 'En pause',
            self::Completed => 'Terminé',
            self::Cancelled => 'Annulé',
        };
    }
}
