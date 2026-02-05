<?php

namespace App\Notifications\Expense;

use App\Models\Expense\ExpenseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ExpenseReport $report) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nouvelle note de frais à valider")
            ->greeting("Bonjour " . $notifiable->name)
            ->line("L'employé {$this->report->user->name} a soumis une nouvelle note de frais : {$this->report->label}.")
            ->line("Montant total TTC : " . number_format($this->report->total_ttc, 2, ',', ' ') . " €")
            // ->action('Voir la note de frais', url('/admin/expense-reports/' . $this->report->id))
            ->line('Merci de traiter cette demande dans les plus brefs délais.');
    }

    public function toArray($notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'user_name' => $this->report->user->name,
            'total_ttc' => $this->report->total_ttc,
            'message' => "Nouvelle note de frais soumise pour validation."
        ];
    }
}
