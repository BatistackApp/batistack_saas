<?php

namespace App\Providers;

use App\Jobs\Pilotage\GlobalKpiOrchestratorJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class PilotageServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(new GlobalKpiOrchestratorJob)
                ->dailyAt('23:45');
        });
    }
}
