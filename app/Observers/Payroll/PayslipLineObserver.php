<?php

namespace App\Observers\Payroll;

use App\Models\Payroll\PayslipLine;

class PayslipLineObserver
{
    public function saved(PayslipLine $line): void
    {
        $this->recalculatePayslip($line);
    }

    public function deleted(PayslipLine $line): void
    {
        $this->recalculatePayslip($line);
    }

    protected function recalculatePayslip(PayslipLine $line): void
    {
        $payslip = $line->payslip;
        if (! $payslip) {
            return;
        } else {
            app(\App\Services\Payroll\PayrollCalculationService::class)->refreshTotals($payslip);
        }
    }
}
