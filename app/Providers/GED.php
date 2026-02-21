<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class GED extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('ged:check-expirations')
                ->dailyAt('03:00');
        });
    }
}
