<?php

namespace App\Enums\Fleet;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum MaintenanceType: string implements HasLabel
{
    case Preventative = 'preventative'; // Entretien planifié
    case Curative = 'curative';         // Panne / Incident
    case Regulatory = 'regulatory';     // Contrôles obligatoires (si non géré par Inspection)

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Preventative => __('fleet.maintenance_types.preventative'),
            self::Curative => __('fleet.maintenance_types.curative'),
            self::Regulatory => __('fleet.maintenance_types.regulatory'),
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::Preventative => __('fleet.maintenance_types.preventative_description'),
            self::Curative => __('fleet.maintenance_types.curative_description'),
            self::Regulatory => __('fleet.maintenance_types.regulatory_description'),
        };
    }
}
