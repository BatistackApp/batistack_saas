<?php

namespace App\Notifications\HR;

use App\Models\HR\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeOnboardedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Employee $employee) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Bienvenue chez nous, {$this->employee->first_name} !")
            ->greeting("Bonjour {$this->employee->first_name},")
            ->line('Nous sommes ravis de vous compter parmi nos effectifs.')
            ->line('Votre fiche RH a été créée avec succès.');
        // ->action('Compléter mon profil', url('/hr/my-profile'));
    }
}
