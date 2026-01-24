<?php

namespace App\Enums\Payroll;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PayrollExportFormat: string implements HasLabel
{
    case Silae = 'silae';
    case Sage = 'sage';
    case Generic = 'generic';


    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Silae => __('payroll.export_format.silae'),
            self::Sage => __('payroll.export_format.sage'),
            self::Generic => __('payroll.export_format.generic'),
        };
    }
}
