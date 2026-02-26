<?php

namespace App\Observers\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Jobs\Payroll\ProcessPayrollImputationJob;
use App\Models\Payroll\PayrollPeriod;

class PayrollPeriodObserver
{
    /**
     * Gère les actions après la mise à jour d'une période.
     */
    public function updated(PayrollPeriod $period): void
    {
        // Si la période passe de 'Draft' à 'Validated'
        if ($period->wasChanged('status') && $period->status === PayrollStatus::Validated) {

            // 1. Lancement de l'imputation analytique vers les projets
            ProcessPayrollImputationJob::dispatch($period);

            // 2. On pourrait aussi déclencher ici un export automatique
            // vers un logiciel de paie tiers (ex: Sage, Silae)
        }
    }
}
