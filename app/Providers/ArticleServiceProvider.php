<?php

namespace App\Providers;

use App\Jobs\Articles\CheckLowStockJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ArticleServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(new CheckLowStockJob)
                ->dailyAt('06:00')
                ->description('Check for low stock articles');

            $schedule->job(new \App\Jobs\Articles\ArchiveUnusedArticleJob)
                ->monthly()
                ->description('Archive unused articles');
        });
    }
}
