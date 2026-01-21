<?php

namespace App\Enums\Commerce;

enum TaxRate: string
{
    case Exempt = 'exempt';
    case Reduced = 'reduced';
    case Normal = 'normal';

    public function percentage(): float
    {
        return match ($this) {
            self::Exempt => 0,
            self::Reduced => 5.5,
            self::Normal => 20,
        };
    }
}
