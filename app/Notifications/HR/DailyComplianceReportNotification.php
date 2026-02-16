<?php

namespace App\Notifications\HR;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class DailyComplianceReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Collection $issues
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Rapport Quotidien de Conformité RH')
            ->greeting('Bonjour,')
            ->line('Voici le récapitulatif des habilitations arrivant à échéance ou expirées :');

        foreach ($this->issues as $issue) {
            $status = $issue->expiry_date->isPast() ? '🔴 EXPIRÉ' : '🟠 Proche';
            $mail->line("- **{$issue->employee->full_name}** : {$issue->skill->name} ({$status} au ".$issue->expiry_date->format('d/m/Y').')');
        }

        return $mail->action('Consulter le tableau de bord', url('/hr/compliance'))
            ->line('Merci de traiter ces éléments pour maintenir la conformité du site.');
    }
}
