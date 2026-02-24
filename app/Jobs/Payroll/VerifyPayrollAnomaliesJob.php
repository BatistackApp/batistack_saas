<?php

namespace App\Jobs\Payroll;

use App\Models\HR\Employee;
use App\Models\Payroll\PayrollPeriod;
use App\Models\User;
use App\Notifications\Payroll\PayrollAnomaliesNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyPayrollAnomaliesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PayrollPeriod $period) {}

    public function handle(): void
    {
        $anomalies = [];

        // 1. Salariés actifs sans aucune heure pointée ni absence
        $employees = Employee::where('tenants_id', $this->period->tenants_id)
            ->where('is_active', true)
            ->get();

        foreach ($employees as $employee) {
            $hasActivity = $employee->timeEntries()
                ->whereBetween('date', [$this->period->start_date, $this->period->end_date])
                ->exists();

            if (! $hasActivity) {
                $anomalies[] = "L'employé {$employee->first_name} {$employee->last_name} n'a aucune activité enregistrée.";
            }
        }

        // 2. Si anomalies trouvées, on notifie le gestionnaire RH/Paie
        if (! empty($anomalies)) {
            $hrAdmins = User::role('tenant_admin')->where('tenants_id', $this->period->tenants_id)->get();
            foreach ($hrAdmins as $admin) {
                $admin->notify(new PayrollAnomaliesNotification($this->period, $anomalies));
            }
        }
    }
}
