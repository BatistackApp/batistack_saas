<?php

namespace App\Observers\Expense;

use App\Models\Expense\ExpenseItem;
use App\Services\Expense\ExpenseCalculationService;

class ExpenseItemObserver
{
    public function __construct(
        protected ExpenseCalculationService $calculationService
    ) {}

    public function saved(ExpenseItem $item): void
    {
        $this->calculationService->refreshReportTotals($item->report);
    }

    public function deleted(ExpenseItem $item): void
    {
        $this->calculationService->refreshReportTotals($item->report);
    }
}
