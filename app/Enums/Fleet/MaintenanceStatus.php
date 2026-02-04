<?php

namespace App\Enums\Fleet;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum MaintenanceStatus: string implements HasLabel, HasColor
{
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Scheduled => 'blue',
            self::InProgress => 'yellow',
            self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Scheduled => __('fleet.maintenance_statuses.scheduled'),
            self::InProgress => __('fleet.maintenance_statuses.in_progress'),
            self::Completed => __('fleet.maintenance_statuses.completed'),
            self::Cancelled => __('fleet.maintenance_statuses.cancelled'),
        };
    }
}
