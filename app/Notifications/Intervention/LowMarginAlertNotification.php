<?php

namespace App\Notifications\Intervention;

use App\Models\Intervention\Intervention;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowMarginAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Intervention $intervention) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("Alerte de Marge : {$this->intervention->reference}")
            ->line("La marge de l'intervention {$this->intervention->label} est inférieure au seuil critique.")
            ->line("Marge actuelle : " . number_format($this->intervention->margin_ht, 2, ',', ' ') . " €");
            // ->action('Vérifier l\'intervention', url('/admin/interventions/' . $this->intervention->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'intervention_id' => $this->intervention->id,
            'reference' => $this->intervention->reference,
            'margin_ht' => $this->intervention->margin_ht,
            'message' => "Alerte : rentabilité faible sur l'intervention {$this->intervention->reference}."
        ];
    }
}
