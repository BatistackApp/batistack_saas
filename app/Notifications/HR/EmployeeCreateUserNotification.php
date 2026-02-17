<?php

namespace App\Notifications\HR;

use App\Models\HR\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeCreateUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Employee $employee, protected string $passwordCase) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Bienvenue chez nous, {$this->employee->full_name} !")
            ->greeting("Bonjour {$this->employee->full_name},")
            ->line('Votre espace employée à été créer avec les identifiants suivants:')
            ->line("L'email est celui que vous avez fournie lors de votre entretien.")
            ->line("Le mot de passe provisoire est: $this->passwordCase");
    }
}
