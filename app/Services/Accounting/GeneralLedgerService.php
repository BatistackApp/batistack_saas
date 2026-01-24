<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingAccounts;
use App\Models\Accounting\AccountingEntryLine;
use App\Models\Core\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GeneralLedgerService
{
    /**
     * Génère le grand livre détaillé pour un compte sur une période
     *
     * @return Collection<array{date: string, reference: string, description: string, debit: float, credit: float, balance: float}>
     */
    public function generateForAccount(
        Tenant $tenant,
        AccountingAccounts $account,
        Carbon $startDate,
        Carbon $endDate,
    ): Collection {
        $lines = AccountingEntryLine::query()
            ->where('account_id', $account->id)
            ->whereHas('entry', function ($query) use ($tenant, $startDate, $endDate) {
                $query->where('tenant_id', $tenant->id)
                    ->whereBetween('posted_at', [$startDate, $endDate])
                    ->whereIn('status', ['posted', 'locked']);
            })
            ->with('entry:id,reference,posted_at,description')
            ->orderBy('id')
            ->get();

        $balance = 0;
        $result = [];

        foreach ($lines as $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;
            $balance += $debit - $credit;

            $result[] = [
                'date' => $line->entry->posted_at->format('Y-m-d'),
                'reference' => $line->entry->reference,
                'description' => $line->entry->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
            ];
        }

        return collect($result);
    }

    /**
     * Génère le grand livre complet pour tous les comptes
     */
    public function generateFull(
        Tenant $tenant,
        Carbon $startDate,
        Carbon $endDate,
    ): Collection {
        $accounts = AccountingAccounts::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        return $accounts->map(function (AccountingAccounts $account) use ($tenant, $startDate, $endDate) {
            return [
                'account' => $account,
                'entries' => $this->generateForAccount($tenant, $account, $startDate, $endDate),
            ];
        });
    }
}
