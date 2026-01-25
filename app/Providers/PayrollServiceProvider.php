<?php

namespace App\Providers;

use App\Enums\Payroll\PayrollStatus;
use App\Jobs\Payroll\GeneratePayrollSlipJob;
use App\Models\Core\Tenant;
use App\Models\Payroll\PayrollSlip;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class PayrollServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->call(function () {
                $tenants = Tenant::all();
                $now = now();
                $lastMonth = $now->subMonth();

                foreach ($tenants as $tenant) {
                    GeneratePayrollSlipJob::dispatch(
                        $tenant,
                        $lastMonth->year,
                        $lastMonth->month
                    );
                }
            })->monthlyOn(1, '00:01');

            $schedule->call(function () {
                PayrollSlip::query()
                    ->where('year', '<', now()->year - 1)
                    ->update(['status' => PayrollStatus::Archived]);
            })->yearlyOn(6, 1, '03:00');
        });
    }
}
