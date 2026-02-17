<?php

namespace App\Notifications\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriverComplianceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Vehicle $vehicle,
        protected User $driver,
        protected string $reason
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('⚠️ Alerte Conformité Flotte : Conducteur non habilité')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Une anomalie de conformité a été détectée sur une affectation active.')
            ->line("Le conducteur **{$this->driver->name}** est actuellement affecté au véhicule **{$this->vehicle->name}** ({$this->vehicle->license_plate}).")
            ->line("💡 **Motif de l'alerte :** {$this->reason}")
            ->action('Gérer les affectations', url('/fleet/assignments'))
            ->line('Merci de régulariser la situation au plus vite pour garantir la sécurité et la conformité légale.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'compliance_alert',
            'title' => 'Défaut de conformité conducteur',
            'vehicle_id' => $this->vehicle->id,
            'vehicle_name' => $this->vehicle->name,
            'driver_id' => $this->driver->id,
            'driver_name' => $this->driver->name,
            'reason' => $this->reason,
            'severity' => 'critical',
        ];
    }
}
