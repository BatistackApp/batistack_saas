<?php

namespace App\Enums\Projects;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProjectPhaseStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pending = 'pending';     // En attente
    case InProgress = 'in_progress'; // En cours
    case OnHold = 'on_hold';      // En pause / Aléa technique
    case Finished = 'finished';   // Terminé / Réceptionné


    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'primary',
            self::InProgress => 'info',
            self::OnHold => 'warning',
            self::Finished => 'success',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Pending => 'heroicon-o-building-2',
            self::InProgress => 'heroicon-o-arrow-left-right',
            self::OnHold => 'heroicon-o-pause',
            self::Finished => 'heroicon-o-check-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Pending => __('projects.phases.status.pending'),
            self::InProgress => __('projects.phases.status.in_progress'),
            self::OnHold => __('projects.phases.status.on_hold'),
            self::Finished => __('projects.phases.status.finished'),
        };
    }
}
