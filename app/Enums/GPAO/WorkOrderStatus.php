<?php

namespace App\Enums\GPAO;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum WorkOrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';           // En conception
    case Planned = 'planned';       // Planifié (Matières réservées)
    case InProgress = 'in_progress'; // En cours de fabrication
    case Completed = 'completed';   // Terminé (Produit fini en stock)
    case Cancelled = 'cancelled';   // Annulé

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Planned => 'blue',
            self::InProgress => 'orange',
            self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-document-text',
            self::Planned => 'heroicon-o-clock',
            self::InProgress => 'heroicon-o-play',
            self::Completed => 'heroicon-o-check',
            self::Cancelled => 'heroicon-o-x',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('work_order.statuses.draft'),
            self::Planned => __('work_order.statuses.planned'),
            self::InProgress => __('work_order.statuses.in_progress'),
            self::Completed => __('work_order.statuses.completed'),
            self::Cancelled => __('work_order.statuses.cancelled'),
        };
    }
}
