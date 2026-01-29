<?php

namespace App\Notifications\Core;

use App\Models\Core\Tenants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantReactivationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tenants $tenant,
    )
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("✅ Votre compte Batistack a été réactivé")
            ->greeting("Bienvenue {$this->tenant->name}")
            ->line("Votre compte Batistack a été réactivé.")
            ->line("Vous pouvez à nouveau accéder à tous vos services.")
            ->action('Accéder à Batistack', url("https://{$this->tenant->slug}.batistack.app"))
            ->line("Merci de nous faire confiance !");
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tenant_id' => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'message' => "Votre compte {$this->tenant->name} a été réactivé.",
            'type' => 'tenant_reactivated',
            'severity' => 'info',
        ];
    }
}
