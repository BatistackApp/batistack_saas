<?php

namespace App\Notifications\Core;

use App\Models\Core\Tenants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantSuspensionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tenants $tenant,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Votre compte Batistack a été suspendu')
            ->greeting("Attention {$this->tenant->name}")
            ->line('Votre compte Batistack a été suspendu suite à un défaut de paiement.')
            ->line('**Raison** : Facture(s) impayée(s) depuis plus de 30 jours.')
            ->action('Vérifier votre situation', url("https://{$this->tenant->slug}.batistack.app/billing"))
            ->line("Veuillez régulariser votre situation au plus vite pour restaurer l'accès.")
            ->line('Contactez notre support si vous avez des questions.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tenant_id' => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'message' => "Votre compte {$this->tenant->name} a été suspendu.",
            'type' => 'tenant_suspended',
            'severity' => 'critical',
        ];
    }
}
