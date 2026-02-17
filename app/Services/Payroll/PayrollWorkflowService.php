<?php

namespace App\Services\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Exceptions\Payroll\PeriodLockedException;
use App\Jobs\Payroll\ProcessPayrollImputationJob;
use App\Models\Payroll\PayrollPeriod;
use App\Notifications\Payroll\PayslipAvailableNotification;
use DB;

/**
 * Service de gestion du cycle de vie (Workflow) de la Paie.
 */
class PayrollWorkflowService
{
    /**
     * Clôture une période de paie et verrouille les données sources.
     */
    public function validatePeriod(PayrollPeriod $period): void
    {
        if ($period->status !== PayrollStatus::Draft) {
            throw new PeriodLockedException;
        }

        DB::transaction(function () use ($period) {
            $period->update(['status' => PayrollStatus::Validated]);

            ProcessPayrollImputationJob::dispatch($period);
            $this->notifyEmployees($period);
        });
    }

    /**
     * Notifie tous les employés dont le bulletin a été validé.
     */
    protected function notifyEmployees(PayrollPeriod $period): void
    {
        $period->payslips()->with('employee.user')->each(function ($payslip) {
            if ($payslip->employee->user) {
                $payslip->employee->user->notify(new PayslipAvailableNotification($payslip));
            }
        });
    }
}
