<?php

namespace App\Enums\Tiers;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TierComplianceStatus: string implements HasColor, HasIcon, HasLabel
{
    case Compliant = 'conforme';
    case ToRenew = 'to_renew';
    case NonCompliantMissing = 'non_compliant_missing';
    case NonCompliantExpired = 'non_compliant_expired';
    case PendingVerification = 'pending_verification';
    case QualificationExpired = 'qualification_expired';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Compliant => 'green',
            self::ToRenew, self::PendingVerification => 'amber',
            self::NonCompliantMissing, self::NonCompliantExpired, self::QualificationExpired => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Compliant => 'heroicon-o-check-circle',
            self::ToRenew => 'heroicon-o-exclamation-circle',
            self::PendingVerification => 'heroicon-o-clock',
            self::NonCompliantMissing, self::NonCompliantExpired, self::QualificationExpired => 'heroicon-o-x-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Compliant => __('tiers.compliance_status.compliant'),
            self::ToRenew => __('tiers.compliance_status.to_renew'),
            self::PendingVerification => __('tiers.compliance_status.pending_verification'),
            self::NonCompliantMissing => __('tiers.compliance_status.non_compliant_missing'),
            self::NonCompliantExpired => __('tiers.compliance_status.non_compliant_expired'),
            self::QualificationExpired => __('tiers.compliance_status.qualification_expired'),
        };
    }
}
