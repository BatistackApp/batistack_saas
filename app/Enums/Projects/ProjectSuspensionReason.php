<?php

namespace App\Enums\Projects;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProjectSuspensionReason: string implements HasLabel
{
    case Weather = 'weather';
    case ClientDecision = 'client_decision';
    case SupplyIssue = 'supply_issue';
    case TechnicalIssue = 'technical_issue';
    case Administrative = 'administrative';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Weather => __('projects.suspension_reasons.weather'),
            self::ClientDecision => __('projects.suspension_reasons.client_decision'),
            self::SupplyIssue => __('projects.suspension_reasons.supply_issue'),
            self::TechnicalIssue => __('projects.suspension_reasons.technical_issue'),
            self::Administrative => __('projects.suspension_reasons.administrative'),
        };
    }
}
