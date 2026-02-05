<?php

namespace App\Enums\GED;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentType: string implements HasLabel
{
    case Contract = 'contract';
    case Invoice = 'invoice';
    case Plan = 'plan';
    case TechnicalDoc = 'technical_doc';
    case Certificate = 'certificate'; // Habilitations, assurances
    case Identity = 'identity';
    case Photo = 'photo';
    case Other = 'other';

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
            self::Other => __('ged.document_types.other'),
        };
    }
}
