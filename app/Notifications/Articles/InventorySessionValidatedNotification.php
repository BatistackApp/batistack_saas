<?php

namespace App\Notifications\Articles;

use App\Models\Articles\InventorySession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InventorySessionValidatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected InventorySession $session)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $warehouseName = $this->session->warehouse->name;
        return (new MailMessage)
            ->subject("✅ Inventaire Validé : Dépôt {$warehouseName}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("La session d'inventaire **{$this->session->reference}** a été validée.")
            ->line("Les stocks du dépôt **{$warehouseName}** ont été régularisés en fonction des comptages physiques.")
            //->action('Consulter le rapport d\'écarts', url("/admin/inventory/sessions/{$this->session->id}"))
            ->line('Le dépôt est désormais dégelé et prêt pour les opérations quotidiennes.');
    }

    public function toArray($notifiable): array
    {
        return [
            'session_id' => $this->session->id,
            'reference' => $this->session->reference,
            'warehouse' => $this->session->warehouse->name,
            'message' => "L'inventaire du dépôt {$this->session->warehouse->name} a été appliqué.",
        ];
    }
}
