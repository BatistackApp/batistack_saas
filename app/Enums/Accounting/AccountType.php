<?php

namespace App\Enums\Accounting;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AccountType: string implements HasLabel
{
    case Asset = 'asset'; // Actif
    case Liability = 'liability'; // Passif
    case Equity = 'equity'; // Capitaux propres
    case Income = 'income'; // Produits
    case Expense = 'expense'; // Charges

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Asset => __('Accounting::account_types.asset'),
            self::Liability => __('Accounting::account_types.liability'),
            self::Equity => __('Accounting::account_types.equity'),
            self::Income => __('Accounting::account_types.income'),
            self::Expense => __('Accounting::account_types.expense'),
        };
    }
}
