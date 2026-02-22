<?php

namespace App\Observers\Expense;

use App\Models\Expense\ExpenseReport;

class ExpenseReportObserver
{
    public function creating(ExpenseReport $report): void
    {
        if (empty($report->label)) {
            $report->label = 'Note de frais - '.now()->format('M Y');
        }
    }
}
