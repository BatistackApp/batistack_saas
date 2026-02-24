<?php

namespace App\Notifications\Payroll;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmitTimesheetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Rappel : Soumission de vos heures de chantier")
            ->greeting("Bonjour " . $notifiable->name)
            ->line("La fin de la période de paie approche et certains de vos pointages sont encore à l'état de brouillon.")
            ->line("Il est impératif de soumettre vos heures pour que vos responsables puissent les valider à temps pour le virement des salaires.")
            ->action('Soumettre mes heures', url('/hr/timesheets'))
            ->line('Merci de votre réactivité pour le bon fonctionnement du service paie.');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => "N'oubliez pas de soumettre vos feuilles d'heures pour validation.",
            'action_url' => '/hr/timesheets',
            'type' => 'reminder'
        ];
    }
}
