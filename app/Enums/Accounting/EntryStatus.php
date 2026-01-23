<?php

namespace App\Enums\Accounting;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum EntryStatus: string implements HasLabel
{
    case Draft = 'draft'; // Brouillon
    case Posted = 'posted'; // Comptabilisée
    case Locked = 'locked'; // Verrouillée

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('accounting.entry_statuses.draft'),
            self::Posted => __('accounting.entry_statuses.posted'),
            self::Locked => __('accounting.entry_statuses.locked'),
        };
    }
}
