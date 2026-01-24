<?php

namespace App\Jobs\HR;

use App\Models\HR\EmployeeTimesheet;
use App\Services\HR\LeaveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ValidateTimesheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly EmployeeTimesheet $timesheet)
    {
    }

    /**
     * @throws \Exception
     */
    public function handle(LeaveService $leaveService): void
    {
        $employee = $this->timesheet->employee;

        // Vérifier s'il y a des conflits avec les congés approuvés
        foreach ($this->timesheet->lines as $line) {
            if ($leaveService->isOnApprovedLeave($employee, $this->timesheet->timesheet_date)) {
                throw new \Exception("L'employé est en congé approuvé pour cette date.");
            }
        }

        // Valider la feuille de pointage
        $this->timesheet->update(['status' => 'validated']);
    }
}
