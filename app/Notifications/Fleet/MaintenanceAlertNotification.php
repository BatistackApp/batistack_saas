<?php

namespace App\Notifications\Fleet;

use App\Models\Fleet\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Vehicle $vehicle, protected string $reason) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("⚠️ Alerte Maintenance Flotte : {$this->vehicle->internal_code}")
            ->line("Le véhicule **{$this->vehicle->name}** ({$this->vehicle->license_plate}) nécessite votre attention.")
            ->line("**Motif :** {$this->reason}")
            // ->action('Gérer le véhicule', url("/admin/fleet/vehicles/{$this->vehicle->id}"))
            ->line('Veuillez planifier l\'intervention pour garantir la sécurité des équipes sur chantier.');
    }

    public function toArray($notifiable): array
    {
        return [
            'vehicle_id' => $this->vehicle->id,
            'vehicle_code' => $this->vehicle->internal_code,
            'reason' => $this->reason,
            'message' => "Alerte maintenance sur le véhicule {$this->vehicle->internal_code}.",
        ];
    }
}
