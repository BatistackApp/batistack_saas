<?php

namespace App\Notifications\Payroll;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingPayrollApprovalsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $pendingCount,
        public string $periodName
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Relance : Approbations de pointages en attente ({$this->periodName})")
            ->greeting('Bonjour '.$notifiable->name)
            ->line("La clôture de la paie pour la période **{$this->periodName}** approche.")
            ->line("Il reste actuellement **{$this->pendingCount} pointage(s)** en attente de votre validation pour vos équipes.")
            ->action('Valider les pointages', url('/hr/timesheets/approvals'))
            ->line('Merci de traiter ces demandes rapidement pour ne pas retarder le virement des salaires.');
    }

    public function toArray($notifiable): array
    {
        return [
            'period_name' => $this->periodName,
            'pending_count' => $this->pendingCount,
            'message' => "Vous avez {$this->pendingCount} pointages à valider pour la paie de {$this->periodName}.",
        ];
    }
}
