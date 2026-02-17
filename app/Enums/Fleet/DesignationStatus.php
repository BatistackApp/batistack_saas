<?php

namespace App\Enums\Fleet;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DesignationStatus: string implements HasColor, HasIcon, HasLabel
{
    case None = 'none';               // Pas de désignation nécessaire
    case Pending = 'pending';         // En attente d'envoi à l'ANTAI
    case Exported = 'exported';       // Exporté (fichier généré mais pas encore envoyé)
    case Sent = 'sent';               // Envoyé (export généré ou API)
    case Confirmed = 'confirmed';     // Confirmé par l'ANTAI

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::None => 'gray',
            self::Pending => 'amber',
            self::Sent => 'blue',
            self::Confirmed => 'green',
            self::Exported => 'indigo',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::None => 'heroicon-o-x-circle',
            self::Pending => 'heroicon-o-clock',
            self::Sent => 'heroicon-o-paper-airplane',
            self::Confirmed => 'heroicon-o-check-circle',
            self::Exported => 'heroicon-o-document',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::None => __('fleet.designation_statuses.none'),
            self::Pending => __('fleet.designation_statuses.pending'),
            self::Sent => __('fleet.designation_statuses.sent'),
            self::Confirmed => __('fleet.designation_statuses.confirmed'),
            self::Exported => __('fleet.designation_statuses.exported'),
        };
    }
}
