<?php

namespace App\Observers\GPAO;

use App\Enums\GPAO\WorkOrderStatus;
use App\Models\GPAO\WorkOrder;
use App\Models\User;
use App\Notifications\GPAO\ProductionFinishedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class WorkOrderObserver
{
    public function creating(WorkOrder $wo): void
    {
        if (empty($wo->reference)) {
            $year = now()->format('Y');
            $latest = WorkOrder::where('reference', 'LIKE', "OF-{$year}-%")
                ->latest('id')
                ->first();

            $number = $latest ? ((int) Str::afterLast($latest->reference, '-') + 1) : 1;
            $wo->reference = "OF-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
        }
    }

    public function updated(WorkOrder $wo): void
    {
        if ($wo->wasChanged('status') && $wo->status === WorkOrderStatus::Completed) {
            $recipients = User::permission('gpao.manage')->get();
            Notification::send($recipients, new ProductionFinishedNotification($wo));
        }
    }
}
