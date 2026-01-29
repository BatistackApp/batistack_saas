<?php

namespace App\Providers;

use App\Jobs\Tiers\CheckTierDocumentExpirationJob;
use App\Jobs\Tiers\SyncTierToExternalServiceJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class TierServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(CheckTierDocumentExpirationJob::class)
                ->daily()
                ->at('09:00')
                ->description('tiers:check-document-expiration')
                ->onFailure(function () {
                    \Log::error('CheckTierDocumentExpirationJob failed');
                });

            $schedule->job(SyncTierToExternalServiceJob::class)
                ->everyThirtyMinutes()
                ->name('tiers:sync-to-external-service');
        });
    }
}
