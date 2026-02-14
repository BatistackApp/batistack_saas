<?php

namespace App\Notifications\Accounting;

use App\Models\Accounting\AccountingEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UnbalancedEntryDetectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private AccountingEntry $entry,
        private string $totalDebit,
        private string $totalCredit,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠️ Écriture déséquilibrée détectée : {$this->entry->reference_number}")
            ->greeting('Attention,')
            ->line("Une écriture déséquilibrée a été créée automatiquement.")
            ->line("Référence : {$this->entry->reference_number}")
            ->line("Débits : {$this->totalDebit} €")
            ->line("Crédits : {$this->totalCredit} €")
            // ->action('Corriger', route('accounting.entries.edit', $this->entry))
            ->line('Veuillez vérifier et corriger cette écriture avant validation.');
    }

    public function toDatabase($notifiable): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => "Écriture déséquilibrée : {$this->entry->reference_number}",
            'message' => "Débits ({$this->totalDebit}) ≠ Crédits ({$this->totalCredit})",
            'entry_id' => $this->entry->id,
            'severity' => 'warning',
        ];
    }
}
