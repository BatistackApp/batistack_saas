<?php

namespace App\Providers;

use App\Jobs\HR\CheckExpiringSkillsJob;
use App\Jobs\HR\HRComplianceSummaryJob;
use Illuminate\Support\ServiceProvider;

class HumanResourceServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

            $schedule->command('hr:check-expiries')
                ->dailyAt('06:00')
                ->withoutOverlapping()
                ->runInBackground();

            $schedule->job(new CheckExpiringSkillsJob)->dailyAt('07:00');
            $schedule->job(new HRComplianceSummaryJob)->weeklyOn(1, '07:00');
        });
    }
}
