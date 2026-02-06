<?php

namespace App\Notifications\Payroll;

use App\Models\Payroll\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayslipAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Payslip $payslip) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $month = $this->payslip->period->name;

        return (new MailMessage)
            ->subject("Votre bulletin de paie de {$month} est disponible")
            ->greeting("Bonjour " . $notifiable->name)
            ->line("Le bulletin de salaire pour la période de {$month} a été validé et est désormais consultable dans votre espace salarié.")
            // ->action('Consulter mon bulletin', url('/my-payslips'))
            ->line("Ceci est un document important, nous vous conseillons de le télécharger et de le conserver.");
    }
    public function toArray($notifiable): array
    {
        return [
            'payslip_id' => $this->payslip->id,
            'period_name' => $this->payslip->period->name,
            'message' => "Votre bulletin de paie de {$this->payslip->period->name} est disponible.",
        ];
    }
}
