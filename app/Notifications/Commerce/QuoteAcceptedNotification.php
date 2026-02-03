<?php

namespace App\Notifications\Commerce;

use App\Models\Commerce\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Quote $quote) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->success()
            ->subject("🎉 Devis Accepté : {$this->quote->reference}")
            ->line("Bonne nouvelle ! Le client **{$this->quote->customer->name}** a accepté le devis **{$this->quote->reference}**.")
            ->line("Projet : {$this->quote->project->name}")
            ->line('Montant Total HT : '.number_format($this->quote->total_ht, 2).' €')
            // ->action('Lancer le chantier', url("/admin/projects/{$this->quote->project_id}"))
            ->line('Vous pouvez maintenant générer les premières commandes de matériaux.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'quote_accepted',
            'reference' => $this->quote->reference,
            'message' => "Le devis {$this->quote->reference} a été signé par le client.",
        ];
    }
}
