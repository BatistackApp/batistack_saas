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
            self::Deposit => __('commerce.invoice.types.deposit'),
            self::Progress => __('commerce.invoice.types.progress'),
            self::Final => __('commerce.invoice.types.final'),
            self::CreditNote => __('commerce.invoice.types.credit_note'),
            self::Normal => __('commerce.invoice.types.normal'),
        };
    }
}
