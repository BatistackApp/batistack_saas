<?php

namespace App\Notifications\Chantiers;

use App\Models\Chantiers\Chantier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChantierBudgetAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Chantier $chantier) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Alerte budget : Chantier {$this->chantier->name}")
            ->line("Le chantier {$this->chantier->name} a atteint {$this->chantier->budget_usage_percent}% du budget.")
            ->action('Voir le chantier', url('/chantiers/'.$this->chantier->id))
            ->line('Merci de surveiller les dépenses.');
    }

    public function toArray($notifiable): array
    {
        return [
            'chantier_id' => $this->chantier->id,
            'chantier_name' => $this->chantier->name,
            'budget_usage_percent' => $this->chantier->budget_usage_percent,
        ];
    }
}
