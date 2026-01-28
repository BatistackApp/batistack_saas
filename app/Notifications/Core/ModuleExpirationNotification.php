<?php

namespace App\Notifications\Core;

use App\Models\Core\ModuleCatalog;
use App\Models\Core\Tenants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModuleExpirationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Tenants $tenant,
        private ModuleCatalog $module,
        private string $type = 'expired', // 'warning' (7j avant), 'expired'
    )
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = $this->type === 'warning'
            ? "Expiration imminente du module {$this->module->name}"
            : "Module {$this->module->name} expiré";

        $message = $this->type === 'warning'
            ? "Votre abonnement au module {$this->module->name} expire dans 7 jours."
            : "Votre abonnement au module {$this->module->name} a expiré. Renouvelez votre souscription.";

        return (new MailMessage)
            ->subject($subject)
            ->line($message)
            ->action('Renouveler', url("https://{$this->tenant->slug}.batistack.app/billing"))
            ->line('Contactez notre support si vous avez des questions.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tenant_id' => $this->tenant->id,
            'module_id' => $this->module->id,
            'module_name' => $this->module->name,
            'type' => $this->type,
            'message' => "Le module {$this->module->name} {$this->typeLabel()}.",
        ];
    }

    private function typeLabel(): string
    {
        return match($this->type) {
            'warning' => 'expire bientôt',
            'expired' => 'a expiré',
        };
    }
}
