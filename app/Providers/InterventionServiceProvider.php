<?php

namespace App\Providers;

use App\Jobs\Intervention\ReminderPlannedInterventionJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class InterventionServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(new ReminderPlannedInterventionJob)
                ->dailyAt('18:00')
                ->description('Rappel des interventions du lendemain aux techniciens')
                ->onOneServer();
        });
    }
}
