<?php

namespace App\Providers;

use App\Jobs\Core\CheckModuleExpirationJob;
use App\Jobs\Core\CheckOverdueInvoicesJob;
use App\Jobs\Core\GenerateTenantReportsJob;
use App\Observers\Core\TenantSubscriptionObserver;
use App\Services\Core\ModuleAccessService;
use App\Services\Core\OvhDomainService;
use App\Services\Core\TenantDatabaseService;
use App\Services\Core\TenantIdentificationService;
use App\Services\Core\TenantModuleManager;
use App\Services\Core\TenantProvisioningService;
use Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Cashier\Subscription;
use Log;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantIdentificationService::class);
        $this->app->singleton(ModuleAccessService::class);
        $this->app->singleton(OvhDomainService::class);
        $this->app->singleton(TenantDatabaseService::class);
        $this->app->singleton(TenantProvisioningService::class);
        $this->app->singleton(TenantModuleManager::class);
    }

    public function boot(): void
    {
        Subscription::observe(TenantSubscriptionObserver::class);
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(CheckModuleExpirationJob::class)
                ->dailyAt('01:00')
                ->name('core:check-module-expiration')
                ->onFailure(function () {
                    \Log::error("CheckModuleExpirationJob failed");
                });

            // Nettoyage des caches tenant (2h du matin)
            $schedule
                ->call(function () {
                    Cache::tags(['tenant'])->flush();
                    Log::info('Tenant caches flushed');
                })
                ->dailyAt('02:00')
                ->name('core:flush-tenant-cache');

            // Génération de rapports (chaque dimanche à 3h du matin)
            $schedule
                ->job(new GenerateTenantReportsJob())
                ->weekly()
                ->sundays()
                ->at('03:00')
                ->name('core:generate-tenant-reports');

            $schedule->job(CheckOverdueInvoicesJob::class)
                ->dailyAt('02:00')
                ->name('core:check-overdue-invoices')
                ->onFailure(function () {
                    \Log::error("CheckOverdueInvoicesJob failed");
                });
        });
    }
}
