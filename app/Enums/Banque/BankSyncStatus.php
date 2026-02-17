<?php

namespace App\Enums\Banque;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum BankSyncStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';         // Synchro OK
    case Error = 'error';           // Problème d'auth (Consentement expiré)
    case Pending = 'pending';       // En attente de première synchro
    case Disconnected = 'disconnected'; // Compte déconnecté par l'utilisateur

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'green',
            self::Error => 'red',
            self::Pending => 'amber',
            self::Disconnected => 'gray',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Active => 'heroicon-o-check-circle',
            self::Error => 'heroicon-o-x-circle',
            self::Pending => 'heroicon-o-clock',
            self::Disconnected => 'heroicon-o-link-removed',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Active => __('bank.sync_status.active'),
            self::Error => __('bank.sync_status.error'),
            self::Pending => __('bank.sync_status.pending'),
            self::Disconnected => __('bank.sync_status.disconnected'),
        };
    }
}
