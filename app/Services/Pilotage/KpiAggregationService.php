<?php

namespace App\Services\Pilotage;

use App\Models\Projects\Project;
use App\Services\Accounting\BalanceCalculator;
use DB;

class KpiAggregationService
{
    public function __construct(
        protected BalanceCalculator $balanceCalculator
    ) {}

    /**
     * Calcule la marge brute d'un projet (Ventes - Coûts).
     */
    public function getProjectGrossMargin(Project $project): string
    {
        // 1. Ventes (Classes 7 liées au projet)
        $revenue = DB::table('accounting_entry_lines')
            ->join('accounting_entries', 'accounting_entry_lines.accounting_entry_id', '=', 'accounting_entries.id')
            ->where('accounting_entry_lines.project_id', $project->id)
            ->where('accounting_entries.status', 'validated')
            ->whereRaw('chart_of_account_id IN (SELECT id FROM chart_of_accounts WHERE account_number LIKE "7%")')
            ->sum('credit');

        // 2. Coûts (Classes 6 + Main d'œuvre chargée)
        $costs = DB::table('accounting_entry_lines')
            ->join('accounting_entries', 'accounting_entry_lines.accounting_entry_id', '=', 'accounting_entries.id')
            ->where('accounting_entry_lines.project_id', $project->id)
            ->where('accounting_entries.status', 'validated')
            ->whereRaw('chart_of_account_id IN (SELECT id FROM chart_of_accounts WHERE account_number LIKE "6%")')
            ->sum('debit');

        if (bccomp((string) $revenue, '0', 4) === 0) {
            return '0';
        }

        // Formule : ((Ventes - Coûts) / Ventes) * 100
        $margin = bcsub((string) $revenue, (string) $costs, 4);
        $ratio = bcdiv($margin, (string) $revenue, 4);

        return bcmul($ratio, '100', 2);
    }

    /**
     * Récupère la trésorerie nette (Comptes 512).
     */
    public function getNetCash(int $tenantId): string
    {
        // Somme des soldes des comptes de trésorerie (512...)
        return DB::table('accounting_entry_lines')
            ->join('accounting_entries', 'accounting_entry_lines.accounting_entry_id', '=', 'accounting_entries.id')
            ->join('chart_of_accounts', 'accounting_entry_lines.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.tenants_id', $tenantId)
            ->where('chart_of_accounts.account_number', 'LIKE', '512%')
            ->where('accounting_entries.status', 'validated')
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->value('balance') ?? '0';
    }
}
