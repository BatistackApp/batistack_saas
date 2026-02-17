<?php

namespace App\Enums\Projects;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProjectUserRole: string implements HasLabel
{
    case ProjectManager = 'project_manager';
    case SiteManager = 'site_manager';
    case Contractor = 'contractor';
    case Other = 'other';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ProjectManager => __('projects.user_role.project_manager'),
            self::SiteManager => __('projects.user_role.site_manager'),
            self::Contractor => __('projects.user_role.contractor'),
            self::Other => __('projects.user_role.other'),
        };
    }
}
