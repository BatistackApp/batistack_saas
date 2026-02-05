<?php

namespace App\Providers;

use App\Jobs\Fleet\SyncAllVehiclesApiDataJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class FleetServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(new SyncAllVehiclesApiDataJob)
                ->dailyAt('03:00')
                ->onOneServer();
        });
    }
}
