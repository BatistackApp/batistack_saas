<?php

namespace App\Notifications\HR;

use App\Enums\HR\TimeEntryStatus;
use App\Models\HR\TimeEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimeEntryStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected TimeEntry $entry) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $status = $this->entry->status;
        $date = $this->entry->date->format('d/m/Y');
        $url = url('/hr/timesheets/'.$this->entry->id);

        $mail = (new MailMessage)->greeting("Bonjour {$notifiable->name},");

        return match ($status) {
            TimeEntryStatus::Rejected => $mail->subject("Pointage refusé - {$date}")
                ->line("Votre pointage du {$date} a été rejeté par l'encadrement.")
                ->line('Motif : '.($this->entry->rejection_note ?? 'Non précisé.'))
                ->action('Corriger mes heures', $url)
                ->error(),
            TimeEntryStatus::Submitted => $mail->subject("Nouveau pointage à valider - {$this->entry->employee->full_name}")
                ->line("L'employé {$this->entry->employee->full_name} a soumis ses heures pour la journée du {$date}.")
                ->line('Projet : '.($this->entry->project->name ?? 'N/A'))
                ->action('Vérifier le pointage', $url),
            TimeEntryStatus::Approved => $mail->subject("Pointage validé - {$date}")
                ->line("Félicitations, votre pointage du {$date} a été définitivement approuvé.")
                ->action('Voir mon relevé', $url),
            default => $mail->subject('Mise à jour de pointage')
                ->line('Le statut de votre pointage a évolué vers : '.$status->value),
        };
    }

    public function toArray($notifiable): array
    {
        return [
            'time_entry_id' => $this->entry->id,
            'status' => $this->entry->status->value,
            'employee_name' => $this->entry->employee->full_name,
            'project_name' => $this->entry->project->name ?? 'N/A',
            'date' => $this->entry->date->format('Y-m-d'),
        ];
    }
}
