<?php

namespace App\Providers;

use App\Enums\Commerce\QuoteStatus;
use App\Jobs\Commerce\ProcessOverdueInvoicesJob;
use App\Models\Commerce\Quote;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class CommerceServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Détecte les factures et situations dont la date d'échéance est dépassée.
            // Ce job met à jour les statuts et envoie les notifications de relance.
            $schedule->job(new ProcessOverdueInvoicesJob)
                ->dailyAt('08:00')
                ->onOneServer();

            // Marque les devis non acceptés comme "Perdus" une fois la date de validité dépassée.
            $schedule->call(function () {
                Quote::where('status', QuoteStatus::Sent)
                    ->where('valid_until', '<', now())
                    ->update(['status' => QuoteStatus::Lost]);
            })->dailyAt('01:00');
        });
    }
}
