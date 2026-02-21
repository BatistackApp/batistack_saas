<?php

namespace App\Providers;

use App\Jobs\Locations\DailyRentalImputationJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class LocationsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Imputation des coûts chaque nuit à 1h du matin
            $schedule->job(new DailyRentalImputationJob)
                ->dailyAt('01:00')
                ->onOneServer();

            $schedule->command('locations:check-alerts')
                ->dailyAt('07:00')
                ->onOneServer();
        });
    }
}
