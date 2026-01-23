<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingAccounts;
use App\Models\Core\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
    ): Collection {
        $accounts = AccountingAccounts::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        $result = [];

        foreach ($accounts as $account) {
            $debit = $account->entryLines()
                ->whereHas('entry', function ($query) use ($tenant, $startDate, $endDate) {
                    $query->where('tenant_id', $tenant->id)
                        ->whereBetween('posted_at', [$startDate, $endDate])
                        ->whereIn('status', ['posted', 'locked']);
                })
                ->sum('debit');

            $credit = $account->entryLines()
                ->whereHas('entry', function ($query) use ($tenant, $startDate, $endDate) {
                    $query->where('tenant_id', $tenant->id)
                        ->whereBetween('posted_at', [$startDate, $endDate])
                        ->whereIn('status', ['posted', 'locked']);
                })
                ->sum('credit');

            if ($debit > 0 || $credit > 0) {
                $result[] = [
                    'account_number' => $account->number,
                    'account_name' => $account->name,
                    'debit' => (float) $debit,
                    'credit' => (float) $credit,
                    'balance' => (float) ($debit - $credit),
                ];
            }
        }

        return collect($result);
    }
}
