<?php

namespace App\Enums\Core;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum TenantModuleStatus: string implements HasLabel, HasColor, HasIcon
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Expired = 'expired';


    public function getColor(): string|array|null
    {
        return match($this) {
            self::Active => 'green',
            self::Suspended => 'yellow',
            self::Expired => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match($this) {
            self::Active => Heroicon::OutlinedCheckCircle,
            self::Suspended => Heroicon::OutlinedExclamationCircle,
            self::Expired => Heroicon::OutlinedXMark,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match($this) {
            self::Active => __('core.tenant_module_status.active'),
            self::Suspended => __('core.tenant_module_status.suspended'),
            self::Expired => __('core.tenant_module_status.expired'),
        };
    }
}
