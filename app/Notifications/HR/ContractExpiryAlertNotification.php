<?php

namespace App\Notifications\HR;

use App\Models\HR\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractExpiryAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Employee $employee) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $daysRemaining = now()->diffInDays($this->employee->contract_end_date);

        return (new MailMessage)
            ->error()
            ->subject('Alerte RH : Fin de contrat imminente')
            ->line("Le contrat de {$this->employee->full_name} arrive à échéance le {$this->employee->contract_end_date->format('d/m/Y')}.")
            ->line("Il reste environ {$daysRemaining} jours pour décider d'un renouvellement ou d'une fin de collaboration.");
        // ->action('Gérer le contrat', url("/admin/hr/employees/{$this->employee->id}/edit"));
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'contract_expiry',
            'employee_name' => $this->employee->full_name,
            'expiry_date' => $this->employee->contract_end_date->format('Y-m-d'),
        ];
    }
}
