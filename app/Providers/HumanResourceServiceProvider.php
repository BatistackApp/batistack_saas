<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HumanResourceServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

            $schedule->command('hr:check-expiries')
                ->dailyAt('06:00')
                ->withoutOverlapping()
                ->runInBackground();
        });
    }
}
