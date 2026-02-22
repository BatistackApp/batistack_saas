<?php

namespace App\Notifications\Intervention;

use App\Models\Intervention\Intervention;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TechnicianScheduleReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Intervention $intervention) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $date = $this->intervention->planned_at?->format('d/m/Y à H:i') ?? 'Demain';

        return (new MailMessage)
            ->subject("Rappel d'intervention : {$this->intervention->reference}")
            ->line("Bonjour {$notifiable->name},")
            ->line("Ceci est un rappel pour votre intervention prévue pour demain.")
            ->line("**Détails :**")
            ->line("- Référence : {$this->intervention->reference}")
            ->line("- Client : {$this->intervention->customer->name}")
            ->line("- Heure prévue : {$date}")
            ->line("- Description : " . Str::limit($this->intervention->description, 100))
            ->action('Voir les détails sur mobile', url('/interventions/' . $this->intervention->id))
            ->line("Bonne journée !");
    }

    public function toArray($notifiable): array
    {
        return [
            'intervention_id' => $this->intervention->id,
            'reference' => $this->intervention->reference,
            'planned_at' => $this->intervention->planned_at,
            'message' => "Rappel : Intervention {$this->intervention->reference} demain.",
        ];
    }
}
