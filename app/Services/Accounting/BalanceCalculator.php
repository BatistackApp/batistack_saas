<?php

namespace App\Services\Accounting;

use App\Enums\Accounting\EntryStatus;
use App\Models\Accounting\ChartOfAccount;
use Cache;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DB;

class BalanceCalculator
{
    /**
     * Calcule le solde d'un compte à une date donnée.
     * Solde = ∑(Débits) - ∑(Crédits)
     */
    public function calculate(ChartOfAccount $account, ?Carbon $asOf = null): string
    {
        $asOf = ($asOf ?? today())->endOfDay();
        $cacheKey = "account_balance.{$account->id}.{$asOf->format('Y-m-d')}";

        return Cache::remember($cacheKey, 3600, function () use ($account, $asOf) {
            $balance = DB::table('accounting_entry_lines')
                ->join('accounting_entries', 'accounting_entry_lines.accounting_entry_id', '=', 'accounting_entries.id')
                ->where('accounting_entry_lines.chart_of_account_id', $account->id)
                ->where('accounting_entries.status', EntryStatus::Validated->value)
                ->where('accounting_entries.accounting_date', '<=', $asOf)
                ->selectRaw('COALESCE(SUM(debit), 0) as total_debit')
                ->selectRaw('COALESCE(SUM(credit), 0) as total_credit')
                ->first();

            return bcsub((string) $balance->total_debit, (string) $balance->total_credit, 4);
        });
    }

    /**
     * Calcule les soldes pour tous les comptes du plan comptable.
     */
    public function calculateAllBalances(?CarbonImmutable $asOf = null): array
    {
        $asOf ??= today();

        $balances = DB::table('accounting_entry_lines')
            ->join('accounting_entries', 'accounting_entry_lines.accounting_entry_id', '=', 'accounting_entries.id')
            ->join('chart_of_accounts', 'accounting_entry_lines.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('accounting_entries.status', 'validated')
            ->where('accounting_entries.accounting_date', '<=', $asOf)
            ->groupBy('chart_of_accounts.id', 'chart_of_accounts.account_number', 'chart_of_accounts.account_label')
            ->selectRaw('chart_of_accounts.id')
            ->selectRaw('chart_of_accounts.account_number')
            ->selectRaw('chart_of_accounts.account_label')
            ->selectRaw('COALESCE(SUM(debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(credit), 0) as total_credit')
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->account_number => [
                    'id' => $row->id,
                    'label' => $row->account_label,
                    'debit' => (string) $row->total_debit,
                    'credit' => (string) $row->total_credit,
                    'balance' => $row->balance,
                ],
            ]);

        return $balances->toArray();
    }

    /**
     * Invalide le cache des soldes (à appeler après une validation d'écriture).
     */
    public function invalidateCache(ChartOfAccount $account, CarbonImmutable $date): void
    {
        Cache::forget("account_balance.{$account->id}.{$date->format('Y-m-d')}");

        // Invalider aussi les jours suivants
        for ($i = 1; $i <= 30; $i++) {
            $nextDate = $date->addDays($i);
            Cache::forget("account_balance.{$account->id}.{$nextDate->format('Y-m-d')}");
        }
    }
}
