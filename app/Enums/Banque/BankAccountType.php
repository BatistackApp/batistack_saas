<?php

namespace App\Enums\Banque;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum BankAccountType: string implements HasLabel
{
    case Current = 'current';
    case Savings = 'savings';
    case Cash = 'cash';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Current => __('bank.account_type.current'),
            self::Savings => __('bank.account_type.savings'),
            self::Cash => __('bank.account_type.cash'),
        };
    }
}
