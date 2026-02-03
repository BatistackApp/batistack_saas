<?php

namespace App\Notifications\Commerce;

use App\Models\Commerce\Invoices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProgressStatementValidatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invoices $invoice) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("🏗️ Situation n°{$this->invoice->situation_number} générée : {$this->invoice->project->name}")
            ->line("L'état d'avancement (Situation n°{$this->invoice->situation_number}) a été validé pour le projet **{$this->invoice->project->name}**.")
            ->line('Montant de la période : '.number_format($this->invoice->total_ht, 2).' € HT')
            ->line('Retenue de garantie (5%) : '.number_format($this->invoice->retenue_garantie_amount, 2).' €')
            // ->action('Consulter la situation', url("/admin/commerce/invoices/{$this->invoice->id}"))
            ->line('La facture sera transmise au client après votre revue finale.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'progress_statement',
            'reference' => $this->invoice->reference,
            'situation_number' => $this->invoice->situation_number,
            'message' => "Nouvelle situation de travaux prête pour le projet {$this->invoice->project->name}.",
        ];
    }
}
