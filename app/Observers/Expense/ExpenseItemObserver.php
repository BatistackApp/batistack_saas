<?php

namespace App\Observers\Expense;

use App\Enums\Expense\ExpenseStatus;
use App\Exceptions\Expense\ReportLockedException;
use App\Models\Expense\ExpenseItem;
use App\Services\Expense\ExpenseCalculationService;

class ExpenseItemObserver
{
    protected ExpenseCalculationService $calculationService;

    public function __construct()
    {
        $this->calculationService = app(ExpenseCalculationService::class);
    }

    /**
     * Empêche la modification d'un item si la note est déjà soumise ou validée.
     *
     * @throws ReportLockedException
     */
    public function saving(ExpenseItem $item): void
    {
        $report = $item->report;
        // Si le rapport n'est pas chargé, on essaie de le charger
        if (! $report && $item->expense_report_id) {
            $report = $item->report()->first();
        }

        if ($report && ! in_array($report->status, [ExpenseStatus::Draft, ExpenseStatus::Rejected])) {
            throw new ReportLockedException("Impossible de modifier une ligne d'une note de frais verrouillée (Statut: {$report->status->value}");
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
        // On s'assure de recharger la relation pour avoir l'objet frais
        $report = $item->report;

        if (! $report && $item->expense_report_id) {
            $report = $item->report()->first();
        }

        if ($report) {
            $this->calculationService->refreshReportTotals($report);
        }
    }
}
