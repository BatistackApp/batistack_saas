<?php

namespace App\Services\Expense;

use App\Models\Expense\ExpenseReport;

/**
 * Service de calcul pour les lignes de frais (TVA, IK, Totaux).
 */
class ExpenseCalculationService
{
    /**
     * Calcule le HT et la TVA à partir d'un montant TTC et d'un taux.
     */
    public function calculateFromTtc(float $ttc, float $taxRate): array
    {
        $ht = $ttc / (1 + ($taxRate / 100));
        $tva = $ttc - $ht;

        return [
            'amount_ht'  => round($ht, 2),
            'amount_tva' => round($tva, 2),
            'amount_ttc' => round($ttc, 2),
        ];
    }

    /**
     * Calcule le montant des indemnités kilométriques.
     */
    public function calculateKm(float $distance, float $ratePerKm): float
    {
        return round($distance * $ratePerKm, 2);
    }

    /**
     * Recalcule les totaux globaux d'une note de frais.
     */
    public function refreshReportTotals(ExpenseReport $report): void
    {
        $totals = $report->items()
            ->selectRaw('SUM(amount_ht) as ht, SUM(amount_tva) as tva, SUM(amount_ttc) as ttc')
            ->first();

        $report->update([
            'total_ht'  => $totals->ht ?? 0,
            'total_tva' => $totals->tva ?? 0,
            'total_ttc' => $totals->ttc ?? 0,
        ]);
    }
}
