<?php

namespace App\Enums\Expense;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ExpenseStatus: string implements HasLabel, HasColor, HasIcon
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';


    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::Approved, self::Paid => 'green',
            self::Rejected => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-document-text',
            self::Submitted => 'heroicon-o-clock',
            self::Approved => 'heroicon-o-check-circle',
            self::Rejected => 'heroicon-o-x-circle',
            self::Paid => 'heroicon-o-currency-dollar',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('expense.statuses.draft'),
            self::Submitted => __('expense.statuses.submitted'),
            self::Approved => __('expense.statuses.approved'),
            self::Rejected => __('expense.statuses.rejected'),
            self::Paid => __('expense.statuses.paid'),
        };
    }
}
