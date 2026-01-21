<?php

namespace App\Enums\Commerce;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum FactureType: string implements HasLabel
{
    case Standard = 'standard';
    case Progress = 'progress';
    case Final = 'final';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Standard => __('commerce.facture_type.standard'),
            self::Progress => __('commerce.facture_type.progress'),
            self::Final => __('commerce.facture_type.final'),
        };
    }
}
