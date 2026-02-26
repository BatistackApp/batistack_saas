<?php

namespace App\Console\Commands\Payroll;

use App\Enums\HR\TimeEntryStatus;
use App\Models\HR\Employee;
use App\Notifications\Payroll\SubmitTimesheetNotification;
use Illuminate\Console\Command;

class NotifyEmployeeSubmitTimesheetCommand extends Command
{
    protected $signature = 'payroll:remind-employees';

    protected $description = 'Rappelle aux salariés de soumettre leurs heures avant la fin de mois';

    public function handle(): void
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        // On cherche les employés qui ont des pointages en 'Draft' sur la semaine en cours
        $employeesToNotify = Employee::whereHas('timeEntries', function ($query) use ($startOfWeek, $endOfWeek) {
            $query->whereBetween('date', [$startOfWeek, $endOfWeek])
                ->where('status', TimeEntryStatus::Draft);
        })->with('user')->get();

        foreach ($employeesToNotify as $employee) {
            if ($employee->user) {
                $employee->user->notify(new SubmitTimesheetNotification);
                $this->info("Rappel envoyé à : {$employee->user->name}");
            }
        }
    }
}
