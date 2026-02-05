<?php

namespace App\Notifications\Expense;

use App\Models\Expense\ExpenseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseStatusChangedNotification extends Notification implements ShouldQueue
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
            ->subject("Mise à jour de votre note de frais")
            ->line("Votre note de frais '{$this->report->label}' est désormais au statut : {$status}.");
            // ->action('Consulter ma note', url('/my-expenses/' . $this->report->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'status' => $this->report->status->value,
            'message' => "Le statut de votre note de frais a été mis à jour : " . $this->report->status->getLabel()
        ];
    }
}
