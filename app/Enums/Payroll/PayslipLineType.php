<?php

namespace App\Enums\Payroll;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PayslipLineType: string implements HasLabel
{
    case Earning = 'earning';           // Gains (Salaire de base, Heures Sup, Primes)
    case Deduction = 'deduction';       // Retenues (Cotisations, Avances)
    case EmployerCost = 'employer';     // Charges Patronales
    case Info = 'info';                 // Informations (Compteurs CP, Heures)

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Earning => __('payroll.lines.earning'),
            self::Deduction => __('payroll.lines.deduction'),
            self::EmployerCost => __('payroll.lines.employer'),
            self::Info => __('payroll.lines.info'),
        };
    }
}
