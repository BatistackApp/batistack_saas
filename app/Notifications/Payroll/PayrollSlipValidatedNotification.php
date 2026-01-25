<?php

namespace App\Notifications\Payroll;

use App\Models\Payroll\PayrollSlip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayrollSlipValidatedNotification extends Notification implements ShouldQueue
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
            ->subject("Fiche de paie validée - {$this->slip->employee->name}")
            ->line("La fiche de paie pour {$this->slip->employee->name} a été validée.")
            ->line("Période : {$this->slip->period_start} à {$this->slip->period_end}")
            ->line("Montant brut : {$this->slip->gross_amount}€")
            ->action('Consulter', url('/payroll/' . $this->slip->uuid))
            ->line('Merci d\'utiliser notre plateforme !');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'data' => "Fiche de paie validée pour {$this->slip->employee->name} ({$this->slip->year}-{$this->slip->month})"
        ];
    }
}
