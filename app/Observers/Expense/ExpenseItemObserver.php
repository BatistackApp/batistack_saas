<?php

namespace App\Observers\Expense;

use App\Enums\Expense\ExpenseStatus;
use App\Models\Expense\ExpenseItem;
use App\Services\Expense\ExpenseCalculationService;
use Illuminate\Validation\ValidationException;

class ExpenseItemObserver
{
    public function __construct(
        protected ExpenseCalculationService $calculationService
    ) {}

    /**
     * Empêche la modification d'un item si la note est déjà soumise ou validée.
     */
    public function saving(ExpenseItem $item): void
    {
        $report = $item->report;
        if ($report && ! in_array($report->status, [ExpenseStatus::Draft, ExpenseStatus::Rejected])) {
            throw ValidationException::withMessages([
                'report' => "Impossible de modifier une ligne d'une note de frais verrouillée (Statut: {$report->status->value}).",
            ]);
        }
    }

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
