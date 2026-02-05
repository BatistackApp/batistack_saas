<?php

namespace App\Services\Expense;

use App\Enums\Expense\ExpenseStatus;
use App\Exceptions\Expense\ApprovalExpenseException;
use App\Exceptions\Expense\EmptyReportException;
use App\Exceptions\Expense\ReportLockedException;
use App\Exceptions\Expense\SubmitExpenseException;
use App\Models\Expense\ExpenseReport;
use DB;

class ExpenseWorkflowService
{
    public function __construct(
        protected ExpenseCalculationService $calculationService
    ) {}

    /**
     * Soumet une note de frais pour validation.
     * * @throws SubmitExpenseException
     * @throws EmptyReportException
     */
    public function submit(ExpenseReport $report): void
    {
        if (!in_array($report->status, [ExpenseStatus::Draft, ExpenseStatus::Rejected])) {
            throw new SubmitExpenseException("Seule une note en brouillon ou rejetée peut être soumise.");
        }

        if ($report->items()->count() === 0) {
            throw new EmptyReportException("Impossible de soumettre une note de frais ne contenant aucune ligne.");
        }

        $report->update([
            'status' => ExpenseStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Approuve une note de frais et déclenche l'imputation chantier.
     * * @throws ApprovalExpenseException
     */
    public function approve(ExpenseReport $report, int $validatorId): void
    {
        if ($report->status !== ExpenseStatus::SUBMITTED) {
            throw new ApprovalExpenseException("Cette note de frais n'est pas en attente de validation.");
        }

        DB::transaction(function () use ($report, $validatorId) {
            $report->update([
                'status' => ExpenseStatus::Approved,
                'validated_at' => now(),
                'validated_by' => $validatorId,
            ]);

            app(ChantierImputationService::class)->imputeReportToChantiers($report);
        });
    }

    /**
     * Rejette une note de frais.
     * * @throws ReportLockedException
     */
    public function reject(ExpenseReport $report): void
    {
        if ($report->status === ExpenseStatus::Paid) {
            throw new ReportLockedException("Impossible de rejeter une note déjà remboursée.");
        }

        $report->update([
            'status' => ExpenseStatus::Rejected,
            'submitted_at' => null,
        ]);
    }
}
