<?php

namespace App\Notifications\GED;

use App\Models\Core\Tenants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotaWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Tenants $tenant, public float $percentage) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Alerte : Quota de stockage GED')
            ->line("Votre entreprise {$this->tenant->name} a utilisé {$this->percentage}% de son espace de stockage (1 Go).")
            ->line('Pour éviter tout blocage lors de vos prochains imports, pensez à libérer de l\'espace ou à souscrire à une extension.');
        // ->action('Gérer mon stockage', url('/settings/storage'));
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => "Quota de stockage à {$this->percentage}%",
            'tenant_id' => $this->tenant->id,
        ];
    }
}
