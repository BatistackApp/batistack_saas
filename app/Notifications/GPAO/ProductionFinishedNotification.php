<?php

namespace App\Notifications\GPAO;

use App\Models\GPAO\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProductionFinishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'work_order_id' => $this->workOrder->id,
            'reference' => $this->workOrder->reference,
            'ouvrage' => $this->workOrder->ouvrage->name,
            'quantity' => $this->workOrder->quantity_produced,
            'message' => "La production de {$this->workOrder->quantity_produced} unités de {$this->workOrder->ouvrage->name} est terminée."
        ];
    }
}
