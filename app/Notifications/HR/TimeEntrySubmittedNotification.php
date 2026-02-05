<?php

namespace App\Notifications\HR;

use App\Models\HR\TimeEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimeEntrySubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TimeEntry $timeEntry) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Action requise : Nouveau pointage soumis')
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("L'employé {$this->timeEntry->employee->full_name} vient de soumettre ses heures pour validation.")
            ->line("Volume horaire : {$this->timeEntry->hours} heures.")
            // ->action('Accéder au dashboard Manager', url('/hr/manager/approvals'))
            ->line('Merci de traiter cette demande rapidement pour le cycle de paie à venir.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'timesheet_submission',
            'employee_id' => $this->timeEntry->employee_id,
            'message' => "Nouveau pointage de {$this->timeEntry->employee->full_name}",
        ];
    }
}
