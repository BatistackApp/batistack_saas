<?php

namespace App\Enums\Tiers;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TierDocumentStatus: string implements HasLabel, HasColor
{
    case Valid = 'valid';
    case ToRenew = 'to_renew';
    case Expired = 'expired';
    case Missing = 'missing';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Valid => 'green',
            self::ToRenew => 'amber',
            self::Expired => 'red',
            self::Missing => 'gray',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Valid => __('tiers.tier_document_status.valid'),
            self::ToRenew => __('tiers.tier_document_status.to_renew'),
            self::Expired => __('tiers.tier_document_status.expired'),
            self::Missing => __('tiers.tier_document_status.missing'),
        };
    }
}
