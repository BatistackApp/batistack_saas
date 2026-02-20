<?php

namespace App\Observers\GPAO;

use App\Enums\GPAO\WorkOrderStatus;
use App\Exceptions\GPAO\InsufficientMaterialException;
use App\Models\GPAO\WorkOrder;
use App\Models\User;
use App\Notifications\GPAO\ProductionFinishedNotification;
use App\Services\GPAO\ProductionOrchestrator;
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
            $wo->reference = "OF-{$year}-".str_pad($number, 4, '0', STR_PAD_LEFT);
        }
    }

    /**
     * @throws InsufficientMaterialException
     */
    public function updated(WorkOrder $wo): void
    {
        if ($wo->wasChanged('status')) {

            // 1. Passage à IN PROGRESS = Consommation des matières
            if ($wo->status === WorkOrderStatus::InProgress && $wo->getOriginal('status') === WorkOrderStatus::Planned) {
                app(ProductionOrchestrator::class)->consumeComponents($wo);
            }

            // 2. Passage à COMPLETED = Notification de fin
            if ($wo->status === WorkOrderStatus::Completed) {
                $recipients = User::permission('gpao.manage')->get();
                Notification::send($recipients, new ProductionFinishedNotification($wo));
            }
        }
    }
}
