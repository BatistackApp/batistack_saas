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

        // OPTIMISATION : Agrégation SQL groupée des flux entrants (Tiers = Client)
        $incomingsByDate = Invoices::where('tenants_id', $tenantId)
            ->whereIn('status', ['validated', 'partially_paid'])
            ->whereHas('tiers.types', fn($q) => $q->where('type', 'client'))
            ->whereBetween('due_date', [$today, $endDate])
            ->select('due_date', DB::raw('SUM(total_ttc) as total')) // Note: total_ttc car net_to_pay est un accesseur
            ->groupBy('due_date')
            ->pluck('total', 'due_date');

        // OPTIMISATION : Agrégation SQL groupée des flux sortants (Tiers = Fournisseur)
        $outgoingsByDate = Invoices::where('tenants_id', $tenantId)
            ->whereIn('status', ['validated', 'partially_paid'])
            ->whereHas('tiers.types', fn($q) => $q->where('type', 'fournisseur'))
            ->whereBetween('due_date', [$today, $endDate])
            ->select('due_date', DB::raw('SUM(total_ttc) as total'))
            ->groupBy('due_date')
            ->pluck('total', 'due_date');

        $runningBalance = $currentBalance;

        for ($i = 0; $i <= $days; $i++) {
            $dateString = $today->copy()->addDays($i)->toDateString();

            $dayIn = (string) ($incomingsByDate[$dateString] ?? '0');
            $dayOut = (string) ($outgoingsByDate[$dateString] ?? '0');

            $runningBalance = bcadd($runningBalance, $dayIn, 2);
            $runningBalance = bcsub($runningBalance, $dayOut, 2);

            $projections[] = [
                'date' => $dateString,
                'in' => (float) $dayIn,
                'out' => (float) $dayOut,
                'balance' => (float) $runningBalance
            ];
        }

        return [
            'initial_balance' => (float) $currentBalance,
            'final_balance' => (float) $runningBalance,
            'data' => $projections
        ];
    }
}
