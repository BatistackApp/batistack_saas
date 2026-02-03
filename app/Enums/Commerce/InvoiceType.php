<?php

namespace App\Enums\Commerce;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum InvoiceType: string implements HasLabel
{
    case Deposit = 'acompte';     // Facture d'acompte
    case Progress = 'situation';   // Situation de travaux (BTP)
    case Final = 'solde';         // Facture de solde finale
    case CreditNote = 'avoir';     // Avoir
    case Normal = 'normale';     // Facture normale


    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Deposit => __('commerce::invoices.deposit'),
            self::Progress => __('commerce::invoices.progress'),
            self::Final => __('commerce::invoices.final'),
            self::CreditNote => __('commerce::invoices.credit_note'),
            self::Normal => __('commerce::invoices.normal'),
        };
    }
}
