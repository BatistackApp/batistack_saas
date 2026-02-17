<?php

namespace App\Enums\HR;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TimeEntryStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';         // En cours de saisie
    case Submitted = 'submitted'; // Envoyé pour validation
    case Verified = 'verified';   // Vérifié par le Chef de Chantier (Niveau 1)
    case Approved = 'approved';   // Approuvé par le Conducteur de Travaux (Niveau 2 - Final)
    case Rejected = 'rejected';   // Rejeté pour correction

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::Verified => 'amber',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil',
            self::Submitted => 'heroicon-o-paper-airplane',
            self::Approved, self::Verified => 'heroicon-o-check',
            self::Rejected => 'heroicon-o-x',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('hr.time_entry_status.draft'),
            self::Submitted => __('hr.time_entry_status.submitted'),
            self::Approved => __('hr.time_entry_status.approved'),
            self::Rejected => __('hr.time_entry_status.rejected'),
            self::Verified => __('hr.time_entry_status.verified'),
        };
    }
}
