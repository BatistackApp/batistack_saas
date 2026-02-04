<?php

namespace App\Providers;

use App\Enums\Banque\BankSyncStatus;
use App\Jobs\Banque\SyncBankTransactionsJob;
use App\Models\Banque\BankAccount;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class BankServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->call(function () {
                BankAccount::where('sync_status', BankSyncStatus::Active)
                    ->where('is_active', true)
                    ->each(fn ($account) => SyncBankTransactionsJob::dispatch($account));
            })->dailyAt('03:00')->name('bank-nightly-sync');
        });
    }
}
