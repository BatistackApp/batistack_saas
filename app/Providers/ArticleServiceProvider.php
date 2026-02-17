<?php

namespace App\Providers;

use App\Jobs\Articles\IdentifyDormantStockJob;
use App\Jobs\Articles\SendStockShortageReportJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ArticleServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(IdentifyDormantStockJob::class)
                ->dailyAt('03:00');

            $schedule->job(SendStockShortageReportJob::class)
                ->dailyAt('07:30')
                ->weekdays();

            $schedule->command('inventory:sync-totals')
                ->dailyAt('01:00');
        });
    }
}
