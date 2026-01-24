<?php

namespace App\Observers\HR;

use App\Models\HR\EmployeeTimesheet;
use App\Notifications\HR\TimesheetSubmittedNotification;

class EmployeeTimesheetObserver
{
    public function updated(EmployeeTimesheet $timesheet): void
    {
        if ($timesheet->wasChanged('status') && $timesheet->status->value === 'submitted') {
            $timesheet->employee->user->notify(new TimesheetSubmittedNotification($timesheet));
        }
    }
}
