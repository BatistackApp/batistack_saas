<?php

namespace App\Notifications\Core;

use App\Models\Core\Tenants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Tenants $tenant,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Bienvenue sur Batistack - {$this->tenant->name}")
            ->greeting("Bienvenue {$this->tenant->name} 🎉")
            ->line('Votre compte Batistack est maintenant actif.')
            ->line("Accédez à votre espace : {$this->tenant->slug}.batistack.app")
            ->action('Accéder à Batistack', url("https://{$this->tenant->slug}.batistack.app"))
            ->line('Si vous avez besoin d\'aide, contactez notre support.')
            ->line('Merci d\'utiliser Batistack !');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tenant_id' => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'message' => "Votre compte {$this->tenant->name} a été activé.",
            'type' => 'tenant_activated',
        ];
    }
}
