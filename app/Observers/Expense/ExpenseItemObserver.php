<?php

namespace App\Observers\Expense;

use App\Exceptions\Expense\ReportLockedException;
use App\Models\Expense\ExpenseItem;
use App\Services\Expense\ExpenseCalculationService;

class ExpenseItemObserver
{
    public function __construct(
        protected ExpenseCalculationService $calculationService
    ) {}

    /**
     * Empêche la modification d'un item si la note est déjà soumise ou validée.
     *
     * @throws ReportLockedException
     */
    public function saving(ExpenseItem $item): void
    {
        $report = $item->report;

        if ($report && ! $report->isEditable()) {
            throw new ReportLockedException(
                "Action impossible : la note de frais [{$report->label}] est en cours de validation ou payée."
            );
        }

        // Automatisation : Si c'est un frais kilométrique, on force le calcul via le service
        if ($item->is_mileage && $item->distance_km > 0) {
            $item->amount_ht = $this->calculationService->calculateMileage(
                $item->report->tenants_id,
                (float) $item->distance_km,
                (int) $item->vehicle_power
            );
            // On recalcule la TVA (souvent 0 sur les IK mais dépend du pays/tenant)
            $item->amount_tva = 0;
            $item->amount_ttc = $item->amount_ht;
        }
    }

    public function saved(ExpenseItem $item): void
    {
        if ($item->report) {
            $this->calculationService->refreshReportTotals($item->report);
        }
    }

    public function deleted(ExpenseItem $item): void
    {
        if ($item->report) {
            $this->calculationService->refreshReportTotals($item->report);
        }
    }
}
