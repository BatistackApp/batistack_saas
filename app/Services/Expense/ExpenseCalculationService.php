<?php

namespace App\Services\Expense;

use App\Models\Expense\ExpenseReport;
use App\Models\User;
use DB;

/**
 * Service de calcul pour les lignes de frais (TVA, IK, Totaux).
 */
class ExpenseCalculationService
{
    public function __construct(
        protected MileageCalculatorService $mileageService
    ) {}

    /**
     * Calcule le HT et la TVA à partir d'un montant TTC et d'un taux.
     */
    public function calculateFromTtc(float $ttc, float $taxRate): array
    {
        $ht = $ttc / (1 + ($taxRate / 100));
        $tva = $ttc - $ht;

        return [
            'amount_ht' => round($ht, 2),
            'amount_tva' => round($tva, 2),
            'amount_ttc' => round($ttc, 2),
        ];
    }

    /**
     * Calcule les IK en fonction du barème du Tenant.
     */
    public function calculateMileage(User $user, float $distance, int $vehiclePower): float
    {
        return $this->mileageService->calculateForUser($user, $vehiclePower, $distance);
    }

    /**
     * Recalcule les totaux globaux d'une note de frais.
     */
    public function refreshReportTotals(ExpenseReport $report): void
    {
        // On force le rafraîchissement des calculs depuis la base
        $totals = DB::table('expense_items')
            ->where('expense_report_id', $report->id)
            ->selectRaw('SUM(amount_ht) as ht, SUM(amount_tva) as tva, SUM(amount_ttc) as ttc')
            ->first();

        $report->update([
            'amount_ht' => $totals->ht ?? 0,
            'amount_tva' => $totals->tva ?? 0,
            'amount_ttc' => $totals->ttc ?? 0,
        ]);
    }
}
