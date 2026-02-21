<?php

namespace App\Enums\GED;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentStatus: string implements HasLabel, HasColor
{
    case Draft = 'draft';
    case PendingValidation = 'pending_validation';
    case Validated = 'validated';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Archived = 'archived';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft, self::Archived => 'gray',
            self::PendingValidation => 'info',
            self::Validated => 'success',
            self::Rejected => 'danger',
            self::Expired => 'warning',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('ged.status.draft'),
            self::PendingValidation => __('ged.status.pending_validation'),
            self::Validated => __('ged.status.validated'),
            self::Rejected => __('ged.status.rejected'),
            self::Expired => __('ged.status.expired'),
            self::Archived => __('ged.status.archived'),
        };
    }
}
