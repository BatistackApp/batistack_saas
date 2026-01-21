<?php

namespace App\Providers;

use App\Jobs\Chantiers\SyncChantierJob;
use App\Services\Chantiers\ChantierService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ChantierServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function (): void {
            /** @var Schedule $schedule */
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(new SyncChantierJob(app(ChantierService::class)))
                ->dailyAt('03:00')
                ->withoutOverlapping();
        });
    }
}
