<?php

namespace App\Enums\GED;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentType: string implements HasLabel, HasIcon
{
    case Contract = 'contract';
    case Invoice = 'invoice';
    case Plan = 'plan';
    case TechnicalDoc = 'technical_doc';
    case Certificate = 'certificate';
    case Identity = 'identity';
    case Photo = 'photo';
    case DriverLicence = 'driver_licence';
    case Other = 'other';

    // Secteur BTP & Administratif
    case Ppsps = 'ppsps';
    case Doe = 'doe';
    case UrssafVigilance = 'urssaf_vigilance';
    case DecennialInsurance = 'decennial_insurance';
    case Kbis = 'kbis';
    case ProfessionalLicense = 'professional_license'; // AIPR, CACES
    case Report = 'report'; // Compte-rendu de chantier
    case SafetySheet = 'safety_sheet'; // Fiche de données de sécurité (FDS)

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Contract => __('ged.document_types.contract'),
            self::Invoice => __('ged.document_types.invoice'),
            self::Plan => __('ged.document_types.plan'),
            self::TechnicalDoc => __('ged.document_types.technical_doc'),
            self::Certificate => __('ged.document_types.certificate'),
            self::Identity => __('ged.document_types.identity'),
            self::Photo => __('ged.document_types.photo'),
            self::DriverLicence => __('ged.document_types.driver_licence'),
            self::Other => __('ged.document_types.other'),
            self::Ppsps => __('ged.document_types.ppsps'),
            self::Doe => __('ged.document_types.doe'),
            self::UrssafVigilance => __('ged.document_types.urssaf_vigilance'),
            self::DecennialInsurance => __('ged.document_types.decennial_insurance'),
            self::Kbis => __('ged.document_types.kbis'),
            self::Report => __('ged.document_types.report'),
            self::SafetySheet => __('ged.document_types.safety_sheet'),
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Plan => 'heroicon-o-map',
            self::Photo => 'heroicon-o-camera',
            self::DecennialInsurance, self::UrssafVigilance => 'heroicon-o-shield-check',
            self::Report => 'heroicon-o-clipboard-document-text',
            default => 'heroicon-o-document-text',
        };
    }
}
