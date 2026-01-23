<?php

namespace App\Enums\Accounting;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum JournalType: string implements HasLabel
{
    case Sales = 'VT'; // Ventes
    case Purchases = 'AC'; // Achats
    case Bank = 'BQ'; // Banque
    case Miscellaneous = 'DV'; // Divers
    case StaffExpenses = 'NDF'; // Notes de frais
    case Locations = 'LOC'; // Locations

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Sales => __('accounting.journal_types.VT'),
            self::Purchases => __('accounting.journal_types.AC'),
            self::Bank => __('accounting.journal_types.BQ'),
            self::Miscellaneous => __('accounting.journal_types.DV'),
            self::StaffExpenses => __('accounting.journal_types.NDF'),
            self::Locations => __('accounting.journal_types.LOC'),
        };
    }
}
