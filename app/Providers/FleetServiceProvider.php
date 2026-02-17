<?php

namespace App\Providers;

use App\Jobs\Fleet\AuditDriverComplianceJob;
use App\Jobs\Fleet\ExportAntaiFinesJob;
use App\Jobs\Fleet\MonthlyFleetImputationJob;
use App\Jobs\Fleet\ProcessFineMatchingJob;
use App\Jobs\Fleet\ScanVehiclesForMaintenanceJob;
use App\Jobs\Fleet\SyncAllVehiclesApiDataJob;
use App\Models\Fleet\VehicleFine;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class FleetServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->job(new SyncAllVehiclesApiDataJob)
                ->dailyAt('03:00')
                ->onOneServer();

            $schedule->job(new MonthlyFleetImputationJob)
                ->monthlyOn(1, '04:00');

            $schedule->job(new ExportAntaiFinesJob)
                ->weeklyOn(1, '05:00')
                ->onOneServer();

            $schedule->job(new ScanVehiclesForMaintenanceJob)
                ->dailyAt('04:30')
                ->onOneServer();

            $schedule->job(new AuditDriverComplianceJob)
                ->dailyAt('03:00')
                ->onOneServer();

            $schedule->call(function () {
                $pendingFines = VehicleFine::where('status', 'received')
                    ->whereNull('user_id')
                    ->get();

                foreach ($pendingFines as $fine) {
                    ProcessFineMatchingJob::dispatch($fine);
                }
            })->dailyAt('04:00');
        });
    }
}
