<?php

namespace App\Enums\Core;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PlanPriority: string implements HasColor, HasLabel
{
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Critical => 'bg-red-500',
            self::High => 'bg-orange-500',
            self::Medium => 'bg-yellow-500',
            self::Low => 'bg-green-500',
            default => null,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Critical => 'Critique',
            self::High => 'Haute',
            self::Medium => 'Moyenne',
            self::Low => 'Basse',
            default => null,
        };
    }
}
