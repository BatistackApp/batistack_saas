<?php

namespace App\Enums\GED;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentType: string implements HasLabel
{
    // Technique & Chantier
    case Plan = 'plan';
    case TechnicalDoc = 'technical_doc';
    case Ppsps = 'ppsps';
    case Doe = 'doe';
    case Photo = 'photo';

    // Administratif & Légal (CRITIQUE)
    case DecennialInsurance = 'decennial_insurance';
    case UrssafVigilance = 'urssaf_vigilance';
    case Kbis = 'kbis';
    case ProfessionalLicense = 'professional_license';
    case Identity = 'identity';
    case Contract = 'contract';
    case Invoice = 'invoice';

    case Other = 'other';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Plan => __('ged.document_types.plan'),
            self::TechnicalDoc => __('ged.document_types.technical_doc'),
            self::Ppsps => __('ged.document_types.ppsps'),
            self::Doe => __('ged.document_types.doe'),
            self::Photo => __('ged.document_types.photo'),
            self::DecennialInsurance => __('ged.document_types.decennial_insurance'),
            self::UrssafVigilance => __('ged.document_types.urssaf_vigilance'),
            self::Kbis => __('ged.document_types.kbis'),
            self::ProfessionalLicense => __('ged.document_types.professional_license'),
            self::Identity => __('ged.document_types.identity'),
            self::Contract => __('ged.document_types.contract'),
            self::Invoice => __('ged.document_types.invoice'),
            self::Other => __('ged.document_types.other'),
        };
    }
}
