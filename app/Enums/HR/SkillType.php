<?php

namespace App\Enums\HR;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum SkillType: string implements HasLabel
{
    case HardSkill = 'hard_skill';         // Compétence technique (Maçonnerie, Menuiserie)
    case Habilitation = 'habilitation';   // Habilitation électrique, travail en hauteur
    case Certification = 'certification'; // CACES, SST, AIPR
    case Medical = 'medical';             // Visite médicale d'aptitude
    case License = 'license';             // Permis de conduire (B, C, EB...)

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::HardSkill => __('hr.skill_type.hard_skill'),
            self::Habilitation => __('hr.skill_type.habilitation'),
            self::Certification => __('hr.skill_type.certification'),
            self::Medical => __('hr.skill_type.medical'),
            self::License => __('hr.skill_type.license'),
        };
    }
}
