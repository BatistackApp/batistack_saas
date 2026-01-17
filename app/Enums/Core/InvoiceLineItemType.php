<?php

namespace App\Enums\Core;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum InvoiceLineItemType: string implements HasLabel
{
    case Plan = 'plan';
    case Module = 'module';
    case Credit = 'credit';
    case Adjustment = 'adjustment';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Plan => 'Plan',
            self::Module => 'Module',
            self::Credit => 'Credit',
            self::Adjustment => 'Adjustment',
            default => null,
        };
    }
}
