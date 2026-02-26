<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class PayrollServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Génération automatique des périodes le 20 de chaque mois
            $schedule->command('payroll:generate-periods')
                ->monthlyOn(30, '01:00')
                ->onOneServer();

            // Rappel de validation tous les lundis matin
            $schedule->command('payroll:remind-approvals')
                ->weeklyOn(1, '08:30')
                ->onOneServer();
        });
    }
}
