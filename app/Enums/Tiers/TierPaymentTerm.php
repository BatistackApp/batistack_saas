<?php

namespace App\Enums\Tiers;

use Filament\Support\Contracts\HasLabel;

enum TierPaymentTerm: string implements HasLabel
{
    case AtReceipt = 'at_receipt';
    case Net15 = 'net_15';
    case Net30 = 'net_30';
    case Net45 = 'net_45';
    case Net60 = 'net_60';
    case EndOfMonth30 = 'end_of_month_30';
    case EndOfMonth60 = 'end_of_month_60';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AtReceipt => __('tiers.tier_payment_term.at_receipt'),
            self::Net15 => __('tiers.tier_payment_term.net_15'),
            self::Net30 => __('tiers.tier_payment_term.net_30'),
            self::Net45 => __('tiers.tier_payment_term.net_45'),
            self::Net60 => __('tiers.tier_payment_term.net_60'),
            self::EndOfMonth30 => __('tiers.tier_payment_term.end_of_month_30'),
            self::EndOfMonth60 => __('tiers.tier_payment_term.end_of_month_60'),
        };
    }
}
