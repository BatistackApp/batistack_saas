<?php

namespace App\Enums\Articles;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum SerialNumberStatus: string implements HasLabel
{
    case InStock = 'in_stock';
    case Assigned = 'assigned'; // Affecté à un chantier/projet
    case Maintenance = 'maintenance';
    case Lost = 'lost';
    case Sold = 'sold';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            SerialNumberStatus::InStock => __('articles.number_status.in_stock'),
            SerialNumberStatus::Assigned => __('articles.number_status.assigned'),
            SerialNumberStatus::Maintenance => __('articles.number_status.maintenance'),
            SerialNumberStatus::Lost => __('articles.number_status.lost'),
            SerialNumberStatus::Sold => __('articles.number_status.sold'),
        };
    }
}
