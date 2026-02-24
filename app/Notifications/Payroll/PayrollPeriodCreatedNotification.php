<?php

namespace App\Notifications\Payroll;

use App\Models\Payroll\PayrollPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayrollPeriodCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PayrollPeriod $period) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nouvelle période de paie créée : {$this->period->name}")
            ->line("Le système a généré automatiquement la période de paie pour le mois prochain.")
            ->line("Nom de la période : **{$this->period->name}**")
            ->line("Dates : du {$this->period->start_date->format('d/m/Y')} au {$this->period->end_date->format('d/m/Y')}")
            ->action('Accéder à la gestion de paie', url('/payroll/periods'))
            ->line('Vous pouvez dès à présent configurer les ajustements spécifiques si nécessaire.');
    }

    public function toArray($notifiable): array
    {
        return [
            'period_id' => $this->period->id,
            'period_name' => $this->period->name,
            'message' => "La période de paie {$this->period->name} a été créée automatiquement.",
        ];
    }
}
