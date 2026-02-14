<?php

namespace App\Jobs\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Services\Accounting\BalanceCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshAccountBalanceCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(BalanceCalculator $calculator): void
    {
        // Recalculer tous les soldes pour la date d'aujourd'hui
        $accounts = ChartOfAccount::where('is_active', true)->get();

        foreach ($accounts as $account) {
            $calculator->calculate($account, today());
        }
    }
}
