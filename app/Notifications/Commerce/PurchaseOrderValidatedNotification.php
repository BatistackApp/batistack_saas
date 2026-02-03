<?php

namespace App\Notifications\Commerce;

use App\Models\Commerce\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderValidatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PurchaseOrder $order) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("🛒 Commande Validée : {$this->order->reference}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le bon de commande **{$this->order->reference}** vient d'être validé pour le projet **{$this->order->project->name}**.")
            ->line("Fournisseur : {$this->order->supplier->name}")
            ->line('Montant HT : '.number_format($this->order->total_ht, 2).' €')
            ->line('Date de livraison prévue : '.($this->order->expected_delivery_date?->format('d/m/Y') ?? 'Non définie'))
            // ->action('Voir la commande', url("/admin/commerce/purchase-orders/{$this->order->id}"))
            ->line('Merci de préparer la réception des matériaux.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'purchase_order',
            'reference' => $this->order->reference,
            'project' => $this->order->project->name,
            'message' => "La commande {$this->order->reference} est prête pour réception.",
        ];
    }
}
