<?php

namespace App\Enums\Pilotage;

use Filament\Support\Contracts\HasColor;

enum ThresholdSeverity: string implements HasColor
{
    case INFO = 'info';
    case WARNING = 'warning';
    case CRITICAL = 'critical';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INFO => 'blue',
            self::WARNING => 'amber',
            self::CRITICAL => 'danger',
        };
    }
}
