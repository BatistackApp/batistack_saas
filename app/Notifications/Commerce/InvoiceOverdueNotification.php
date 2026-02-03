<?php

namespace App\Notifications\Commerce;

use App\Models\Commerce\Invoices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification implements ShouldQueue
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
            ->error()
            ->subject("⚠️ Alerte Retard de Paiement : {$this->invoice->reference}")
            ->line("La facture **{$this->invoice->reference}** (Projet: {$this->invoice->project->name}) est arrivée à échéance le ".$this->invoice->due_date->format('d/m/Y').'.')
            ->line('Le montant de '.number_format($this->invoice->total_ttc, 2).' € est toujours en attente de règlement.')
            // ->action('Effectuer une relance', url("/admin/commerce/invoices/{$this->invoice->id}"))
            ->line('Un rappel automatique a été envoyé au client.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'invoice_overdue',
            'reference' => $this->invoice->reference,
            'amount' => $this->invoice->total_ttc,
            'message' => "Retard de paiement détecté sur la facture {$this->invoice->reference}.",
        ];
    }
}
