<?php

namespace App\Enums\Projects;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProjectAmendmentStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Refused = 'refused';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Pending => __('projects.amendments_status.pending'),
            self::Accepted => __('projects.amendments_status.accepted'),
            self::Refused => __('projects.amendments_status.refused'),
        };
    }
}
