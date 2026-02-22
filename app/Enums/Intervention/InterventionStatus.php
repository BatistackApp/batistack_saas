<?php

namespace App\Enums\Intervention;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum InterventionStatus: string implements HasColor, HasIcon, HasLabel
{
    case Planned = 'planned';       // Planifiée au planning
    case OnRoute = 'on_route';     // Technicien en cours de trajet
    case InProgress = 'in_progress'; // Travail en cours sur site
    case OnHold = 'on_hold';       // En attente (pièce, décision, accès)
    case Postponed = 'postponed';   // Reportée à une date ultérieure
    case Completed = 'completed';   // Terminée (Rapport signé, stock sorti)
    case Cancelled = 'cancelled';   // Annulée
    case Invoiced = 'invoiced';     // Déjà facturée

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Planned, self::Cancelled => 'gray',
            self::OnRoute => 'warning',
            self::InProgress => 'info',
            self::OnHold => 'danger',
            self::Postponed => 'orange',
            self::Completed, self::Invoiced => 'success',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Planned => 'heroicon-o-calendar',
            self::OnRoute => 'heroicon-o-truck',
            self::InProgress => 'heroicon-o-play',
            self::OnHold => 'heroicon-o-pause',
            self::Postponed => 'heroicon-o-forward',
            self::Completed => 'heroicon-o-check-badge',
            self::Cancelled => 'heroicon-o-x-circle',
            self::Invoiced => 'heroicon-o-currency-euro',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Planned => __('intervention.statuses.planned'),
            self::OnRoute => __('intervention.statuses.on_route'),
            self::InProgress => __('intervention.statuses.in_progress'),
            self::OnHold => __('intervention.statuses.on_hold'),
            self::Postponed => __('intervention.statuses.postponed'),
            self::Completed => __('intervention.statuses.completed'),
            self::Cancelled => __('intervention.statuses.cancelled'),
            self::Invoiced => __('intervention.statuses.invoiced'),
        };
    }
}
