<?php

namespace App\Enums\Intervention;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum InterventionStatus: string implements HasColor, HasIcon, HasLabel
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Invoiced = 'invoiced';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Planned => 'gray',
            self::InProgress => 'blue',
            self::Completed => 'green',
            self::Cancelled => 'red',
            self::Invoiced => 'orange',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Planned => 'heroicon-o-clock',
            self::InProgress => 'heroicon-o-play',
            self::Completed => 'heroicon-o-check',
            self::Cancelled => 'heroicon-o-x',
            self::Invoiced => 'heroicon-o-currency-dollar',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Planned => __('intervention.statuses.planned'),
            self::InProgress => __('intervention.statuses.in_progress'),
            self::Completed => __('intervention.statuses.completed'),
            self::Cancelled => __('intervention.statuses.cancelled'),
            self::Invoiced => __('intervention.statuses.invoiced'),
        };
    }
}
