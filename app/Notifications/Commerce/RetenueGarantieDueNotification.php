<?php

namespace App\Notifications\Commerce;

use App\Models\Commerce\Invoices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RetenueGarantieDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Invoices $invoice) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("💰 Libération de Retenue de Garantie : {$this->invoice->reference}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("La retenue de garantie pour le projet **{$this->invoice->project->name}** arrive à échéance.")
            ->line("Client : {$this->invoice->tiers->name}")
            ->line('Montant à récupérer : **'.number_format($this->invoice->retenue_garantie_amount, 2).' €**')
            ->line('Date de libération prévue : '.$this->invoice->retenue_garantie_release_date->format('d/m/Y'))
            // ->action('Gérer la libération', url("/admin/commerce/invoices/{$this->invoice->id}"))
            ->line('Pensez à préparer le procès-verbal de réception pour débloquer les fonds.');
    }

    public function toArray($notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'amount' => $this->invoice->retenue_garantie_amount,
            'project' => $this->invoice->project->name,
            'message' => "La RG de {$this->invoice->retenue_garantie_amount} € est due pour le projet {$this->invoice->project->name}.",
        ];
    }
}
