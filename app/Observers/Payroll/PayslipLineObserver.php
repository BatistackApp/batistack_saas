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
        if (!$payslip) return;

        $lines = $payslip->lines;

        $gross = $lines->sum('amount_gain');
        $deductions = $lines->sum('amount_deduction');

        // Calcul simplifié du Net à payer
        $netToPay = $gross - $deductions;

        $payslip->updateQuietly([
            'gross_amount' => $gross,
            'net_to_pay' => $netToPay,
        ]);
    }
}
