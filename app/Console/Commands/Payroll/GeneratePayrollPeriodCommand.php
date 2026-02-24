<?php

namespace App\Console\Commands\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Models\Core\Tenants;
use App\Models\Payroll\PayrollPeriod;
use App\Notifications\Payroll\PayrollPeriodCreatedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GeneratePayrollPeriodCommand extends Command
{
    protected $signature = 'payroll:generate-periods';

    protected $description = 'Génère automatiquement la période de paie du mois suivant pour tous les tenants';

    public function handle(): void
    {
        $nextMonth = Carbon::now()->addMonth();
        $start = $nextMonth->copy()->startOfMonth();
        $end = $nextMonth->copy()->endOfMonth();
        $name = "Paie " . $nextMonth->translatedFormat('F Y');

        Tenants::all()->each(function ($tenant) use ($start, $end, $name) {
            $exists = PayrollPeriod::where('tenants_id', $tenant->id)
                ->where('start_date', $start->format('Y-m-d'))
                ->exists();

            if (!$exists) {
                $period = PayrollPeriod::create([
                    'tenants_id' => $tenant->id,
                    'name' => $name,
                    'start_date' => $start,
                    'end_date' => $end,
                    'status' => PayrollStatus::Draft,
                ]);
                $this->info("Période créée pour le tenant: {$tenant->name}");
            } else {
                $period = PayrollPeriod::where('tenants_id', $tenant->id)
                    ->where('start_date', $start->format('Y-m-d'))
                    ->first();
            }

            $tenant->notify(new PayrollPeriodCreatedNotification($period));
        });
    }
}
