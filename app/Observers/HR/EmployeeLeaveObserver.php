<?php

namespace App\Observers\HR;

use App\Models\HR\EmployeeLeave;
use App\Notifications\HR\LeaveApprovedNotification;
use App\Notifications\HR\LeaveRejectedNotification;

class EmployeeLeaveObserver
{
    public function updated(EmployeeLeave $leave): void
    {
        if ($leave->wasChanged('status')) {
            match ($leave->status->value) {
                'approved' => $leave->employee->user->notify(new LeaveApprovedNotification($leave)),
                'rejected' => $leave->employee->user->notify(new LeaveRejectedNotification($leave)),
                default => null,
            };
        }
    }
}
