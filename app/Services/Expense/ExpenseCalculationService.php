<?php

namespace App\Services\Expense;

use App\Models\Expense\ExpenseReport;
use DB;

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
            'amount_ht' => round($ht, 2),
            'amount_tva' => round($tva, 2),
            'amount_ttc' => round($ttc, 2),
        ];
    }

    /**
     * Calcule les IK en fonction du barème du Tenant.
     */
    public function calculateMileage(int $tenantId, float $distance, int $vehiclePower): float
    {
        // On récupère le barème spécifique au tenant pour l'année en cours
        $scale = DB::table('expense_mileage_scales')
            ->where('tenants_id', $tenantId)
            ->where('vehicle_power', $vehiclePower)
            ->where('active_year', now()->year)
            ->first();

        // Fallback sur un barème par défaut si non configuré
        $rate = $scale ? $scale->rate_per_km : 0.0000;

        return round($distance * $rate, 2);
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
            'amount_ht' => $totals->ht ?? 0,
            'amount_tva' => $totals->tva ?? 0,
            'amount_ttc' => $totals->ttc ?? 0,
        ]);
    }

    public function calculateKm(mixed $distance, float $ratePerKm): float
    {
        return round($distance * $ratePerKm, 2);
    }
}
