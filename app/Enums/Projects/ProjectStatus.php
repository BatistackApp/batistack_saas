<?php

namespace App\Enums\Projects;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProjectStatus: string implements HasLabel, HasColor, HasIcon
{
    case Study = 'study';       // En phase d'étude / Devis
    case InProgress = 'in_progress'; // Chantier ouvert
    case Suspended = 'suspended';   // Chantier à l'arrêt
    case Finished = 'finished';     // Réceptionné
    case Archived = 'archived';     // Clôturé administrativement
    case Cancelled = 'cancelled';   // Annulé

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Study => 'purple',
            self::InProgress => 'blue',
            self::Suspended => 'yellow',
            self::Finished => 'green',
            self::Archived => 'gray',
            self::Cancelled => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Study => 'heroicon-o-eye',
            self::InProgress => 'heroicon-o-building-2',
            self::Suspended => 'heroicon-o-pause',
            self::Finished => 'heroicon-o-check-circle',
            self::Archived => 'heroicon-o-archive',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Study => __('projects.status.study'),
            self::InProgress => __('projects.status.in_progress'),
            self::Suspended => __('projects.status.suspended'),
            self::Finished => __('projects.status.finished'),
            self::Archived => __('projects.status.archived'),
            self::Cancelled => __('projects.status.cancelled'),
        };
    }
}
