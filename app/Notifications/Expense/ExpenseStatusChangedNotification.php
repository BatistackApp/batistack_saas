<?php

namespace App\Notifications\Expense;

use App\Enums\Expense\ExpenseStatus;
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
        $statusLabel = $this->report->status->getLabel();

        $message = (new MailMessage)
            ->subject("Mise à jour de votre note de frais : {$statusLabel}")
            ->line("Votre note de frais '{$this->report->label}' a été mise à jour.")
            ->line("Nouveau statut : **{$statusLabel}**.");

        if ($this->report->status === ExpenseStatus::Rejected && $this->report->rejection_reason) {
            $message->line("Motif du refus : {$this->report->rejection_reason}");
        }

        return $message->action('Consulter ma note', url('/expenses/'.$this->report->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'status' => $this->report->status->value,
            'message' => "Votre note de frais est désormais : " . $this->report->status->getLabel(),
            'reason' => $this->report->rejection_reason ?? null
        ];
    }
}
