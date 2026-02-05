<?php

namespace App\Enums\HR;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ContractType: string implements HasLabel
{
    case CDI = 'cdi';
    case CDD = 'cdd';
    case Freelance = 'freelance';
    case Interim = 'interim';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::CDI => __('hr.contract_type.cdi'),
            self::CDD => __('hr.contract_type.cdd'),
            self::Freelance => __('hr.contract_type.freelance'),
            self::Interim => __('hr.contract_type.interim'),
        };
    }
}
