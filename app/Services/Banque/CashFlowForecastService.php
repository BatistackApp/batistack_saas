<?php

namespace App\Services\Banque;

use App\Models\Banque\BankAccount;
use App\Models\Commerce\Invoices;
use DB;

/**
 * Service de projection de trésorerie à 30/60/90 jours (Issue #32).
 */
class CashFlowForecastService
{
    /**
     * Génère une projection complète.
     */
    public function getForecast(int $tenantId, int $days = 90): array
    {
        $currentBalance = (string) BankAccount::where('tenants_id', $tenantId)->sum('current_balance');
        $projections = [];

        $today = now()->startOfDay();
        $endDate = now()->addDays($days)->endOfDay();

        // 1. Récupération des encaissements prévus (Invoices)
        $incomings = Invoices::where('tenants_id', $tenantId)
            ->whereIn('status', ['validated', 'partially_paid'])
            ->whereBetween('due_date', [$today, $endDate])
            ->orderBy('due_date')
            ->get();

        // 2. Récupération des décaissements prévus (Supplier Invoices)
        $outgoings = DB::table('supplier_invoices')
            ->where('tenants_id', $tenantId)
            ->whereIn('status', ['validated', 'partially_paid'])
            ->whereBetween('due_date', [$today, $endDate])
            ->orderBy('due_date')
            ->get();

        // 3. Construction de la courbe temporelle
        $runningBalance = $currentBalance;

        // On itère par jour pour créer les points du graphique
        for ($i = 0; $i <= $days; $i++) {
            $date = $today->copy()->addDays($i);

            $dayIn = $incomings->where('due_date', $date->toDateString())->sum('net_to_pay');
            $dayOut = $outgoings->where('due_date', $date->toDateString())->sum('total_ttc');

            $runningBalance = bcadd($runningBalance, (string)$dayIn, 2);
            $runningBalance = bcsub($runningBalance, (string)$dayOut, 2);

            $projections[] = [
                'date' => $date->toDateString(),
                'in' => $dayIn,
                'out' => $dayOut,
                'balance' => $runningBalance
            ];
        }

        return [
            'current_balance' => $currentBalance,
            'forecast_end_balance' => $runningBalance,
            'data' => $projections
        ];
    }
}
