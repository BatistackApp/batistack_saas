<?php

namespace App\Observers\GPAO;

use App\Enums\GPAO\OperationStatus;
use App\Models\GPAO\WorkOrderOperation;
use App\Models\User;
use App\Notifications\GPAO\ReadyForControlNotification;
use Illuminate\Support\Facades\Notification;

class WorkOrderOperationObserver
{
    public function updated(WorkOrderOperation $operation): void
    {
        /**
         * Si toutes les opérations sont terminées, on peut suggérer la clôture de l'OF.
         */
        if ($operation->wasChanged('status') && $operation->status === OperationStatus::Finished) {
            $wo = $operation->workOrder;

            $remainingOperations = $wo->operations()
                ->where('status', '!=', OperationStatus::Finished)
                ->count();

            if ($remainingOperations === 0) {
                $recipients = User::permission('gpao.manage')->get();

                Notification::send($recipients, new ReadyForControlNotification($wo));
            }
        }
    }
}
