<?php

namespace App\Jobs\HR;

use App\Models\HR\Employee;
use App\Services\HR\TimesheetService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMonthlyPayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $month, private readonly int $year)
    {
    }

    public function handle(TimesheetService $timesheetService): void
    {
        $startDate = Carbon::createFromDate($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $employees = Employee::where('status', true)->get();

        foreach ($employees as $employee) {
            $hours = $timesheetService->getTotalHours($employee, $startDate, $endDate);

            // Enregistrer les données de paie
            // À compléter selon votre module de paie
        }
    }
}
