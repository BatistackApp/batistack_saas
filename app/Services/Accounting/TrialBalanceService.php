<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingAccounts;
use App\Models\Core\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrialBalanceService
{
    /**
     * Génère la balance générale consolidée
     *
     * @return Collection<array{account_number: string, account_name: string, debit: float, credit: float, balance: float}>
     */
    public function generate(
        Tenant $tenant,
        Carbon $startDate,
        Carbon $endDate,
    ): Collection
    {
        return DB::table('accounting_entry_lines')
            ->join('accounting_entries', 'accounting_entry_lines.accounting_entry_id', '=', 'accounting_entries.id')
            ->join('accounting_accounts', 'accounting_entry_lines.accounting_accounts_id', '=', 'accounting_accounts.id')
            ->where('accounting_accounts.tenant_id', $tenant->id)
            ->where('accounting_accounts.is_active', true)
            ->whereBetween('accounting_entries.posted_at', [$startDate, $endDate])
            ->whereIn('accounting_entries.status', ['posted', 'locked'])
            ->select(
                'accounting_accounts.number as account_number',
                'accounting_accounts.name as account_name',
                DB::raw('SUM(accounting_entry_lines.debit) as debit'),
                DB::raw('SUM(accounting_entry_lines.credit) as credit')
            )
            ->groupBy('accounting_accounts.id', 'accounting_accounts.number', 'accounting_accounts.name')
            ->get()
            ->map(function ($row) {
                $row->balance = $row->debit - $row->credit;
                return (array)$row;
            });
    }
}
