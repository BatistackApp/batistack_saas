<?php

namespace App\Notifications\Payroll;

use App\Models\Payroll\PayrollSlip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayrollSlipExportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PayrollSlip $slip)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Fiche de paie exportée - {$this->slip->employee->name}")
            ->line("La fiche de paie pour {$this->slip->employee->name} a été exportée.")
            ->line("Vous pouvez maintenant télécharger le fichier CSV.")
            ->action('Télécharger', url('/payroll/exports/' . $this->slip->uuid))
            ->line('Merci d\'utiliser notre plateforme !');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'data' => "Fiche de paie exportée pour {$this->slip->employee->name} - Export prêt au téléchargement"
        ];
    }
}
