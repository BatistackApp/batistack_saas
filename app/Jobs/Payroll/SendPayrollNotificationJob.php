<?php

namespace App\Jobs\Payroll;

use App\Models\Payroll\PayrollSlip;
use App\Notifications\Payroll\PayrollSlipExportedNotification;
use App\Notifications\Payroll\PayrollSlipValidatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendPayrollNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PayrollSlip $slip,
                                public string $event
    ) {}

    public function handle(): void
    {
        // Récupérer les responsables RH
        $users = $this->slip->tenant->users()
            ->whereHas('roles', fn ($q) => $q->where('name', 'HR Manager'))
            ->get();

        $notification = match ($this->event) {
            'validated' => new PayrollSlipValidatedNotification($this->slip),
            'exported' => new PayrollSlipExportedNotification($this->slip),
            default => null,
        };

        if ($notification && $users->isNotEmpty()) {
            Notification::send($users, $notification);
        }
    }
}
