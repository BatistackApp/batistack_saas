<?php

namespace App\Enums\Banque;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum BankPaymentMethod: string implements HasLabel
{
    case TransferIncoming = 'transfer_incoming';
    case TransferOutgoing = 'transfer_outgoing';
    case Check = 'check';
    case Cash = 'cash';
    case Card = 'card';
    case LCR = 'lcr';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::TransferIncoming => __('bank.payment_methods.transfer_incoming'),
            self::TransferOutgoing => __('bank.payment_methods.transfer_outgoing'),
            self::Check => __('bank.payment_methods.check'),
            self::Cash => __('bank.payment_methods.cash'),
            self::Card => __('bank.payment_methods.card'),
            self::LCR => __('bank.payment_methods.lcr'),
        };
    }
}
