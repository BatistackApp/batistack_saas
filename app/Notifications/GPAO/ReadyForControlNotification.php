<?php

namespace App\Notifications\GPAO;

use App\Models\GPAO\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReadyForControlNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("OF Prêt pour Contrôle : {$this->workOrder->reference}")
            ->line("Toutes les étapes de fabrication pour l'OF {$this->workOrder->reference} sont terminées.")
            ->line("L'ordre de fabrication est désormais en attente de contrôle final et de clôture.");
            // ->action('Vérifier l\'OF', url('/admin/gpao/work-orders/' . $this->workOrder->id));
    }

    public function toDatabase($notifiable): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        return [
            'work_order_id' => $this->workOrder->id,
            'reference' => $this->workOrder->reference,
            'message' => "L'OF {$this->workOrder->reference} est prêt pour le contrôle final."
        ];
    }
}
