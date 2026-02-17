<?php

namespace App\Enums\GPAO;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum OperationStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Running = 'running';
    case Paused = 'paused';
    case Finished = 'finished';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Running => 'blue',
            self::Paused => 'yellow',
            self::Finished => 'green',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Running => 'heroicon-o-play',
            self::Paused => 'heroicon-o-pause',
            self::Finished => 'heroicon-o-check',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Pending => __('operation.statuses.pending'),
            self::Running => __('operation.statuses.running'),
            self::Paused => __('operation.statuses.paused'),
            self::Finished => __('operation.statuses.finished'),
        };
    }
}
