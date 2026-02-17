<?php

namespace App\Enums\Core;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum TenantStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Archived = 'archived';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'green',
            self::Suspended => 'yellow',
            self::Archived => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Active => Heroicon::OutlinedCheckCircle,
            self::Suspended => Heroicon::OutlinedExclamationCircle,
            self::Archived => Heroicon::OutlinedArchiveBoxArrowDown,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Active => __('core.tenant_status.active'),
            self::Suspended => __('core.tenant_status.suspended'),
            self::Archived => __('core.tenant_status.archived'),
        };
    }
}
