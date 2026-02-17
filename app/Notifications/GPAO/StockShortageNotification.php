<?php

namespace App\Notifications\GPAO;

use App\Models\GPAO\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockShortageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder, public string $articleName) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("Alerte Stock GPAO : {$this->workOrder->reference}")
            ->line("Impossible de lancer la production pour l'OF {$this->workOrder->reference}.")
            ->line("Le composant suivant est en rupture : {$this->articleName}");
        // ->action('Gérer les approvisionnements', url('/admin/gpao/work-orders/' . $this->workOrder->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'work_order_id' => $this->workOrder->id,
            'reference' => $this->workOrder->reference,
            'article' => $this->articleName,
            'message' => "Rupture de stock sur le composant {$this->articleName} pour l'OF {$this->workOrder->reference}.",
        ];
    }
}
