<?php

namespace App\Enums\Core;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum InvoiceStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Paid = 'paid';
    case Void = 'void';
    case Uncollectible = 'uncollectible';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'bg-gray-300',
            self::Pending => 'bg-yellow-500',
            self::Paid => 'bg-green-500',
            self::Void => 'bg-red-500',
            self::Uncollectible => 'bg-gray-500',
            default => null,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Void => 'Void',
            self::Uncollectible => 'Uncollectible',
            default => null,
        };
    }
}
