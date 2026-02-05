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
        $this->updateReportTotals($item);
    }

    public function deleted(ExpenseItem $item): void
    {
        $this->updateReportTotals($item);
    }

    private function updateReportTotals(ExpenseItem $item): void
    {
        $item->load('report');

        if ($item->report) {
            $this->calculationService->refreshReportTotals($item->report);
        }
    }
}
