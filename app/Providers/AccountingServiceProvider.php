<?php

namespace App\Providers;

use App\Jobs\Accounting\RefreshAccountBalanceCacheJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class AccountingServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(new RefreshAccountBalanceCacheJob)
                ->dailyAt('02:00');

            $schedule->call(function () {
                // Vérifier les séquences de numérotation chaque jour à 3h du matin
                $journalService = app(\App\Services\Accounting\SequenceNumberGenerator::class);
                $journals = \App\Models\Accounting\Journal::where('is_active', true)->get();

                foreach ($journals as $journal) {
                    $gaps = $journalService->validateSequenceIntegrity(
                        $journal,
                        now()->startOfMonth(),
                        now()->endOfMonth()
                    );

                    if (! empty($gaps)) {
                        \Illuminate\Support\Facades\Log::warning(
                            "Trous de séquence détectés pour journal {$journal->code}",
                            $gaps
                        );
                    }
                }
            })->dailyAt('03:00');

            $schedule->call(function () {
                \App\Models\Accounting\AccountingEntry::where('status', 'draft')
                    ->where('updated_at', '<', now()->subDays(30))
                    ->delete();
            })->daily();
        });
    }
}
