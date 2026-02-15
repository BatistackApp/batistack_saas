<?php

namespace App\Notifications\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleMaintenancePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Vehicle $vehicle,
        protected VehicleMaintenancePlan $plan
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠️ Échéance de Maintenance : {$this->vehicle->internal_code}")
            ->line("Le véhicule **{$this->vehicle->name}** a atteint un seuil critique défini dans le plan : **{$this->plan->name}**.")
            ->line("Compteurs actuels : {$this->vehicle->current_odometer} km / {$this->vehicle->current_hours} h.")
            ->action('Planifier l\'intervention', url("/admin/fleet/maintenances/create?vehicle_id={$this->vehicle->id}&plan_id={$this->plan->id}"))
            ->line('Une maintenance préventive rapide permet d\'éviter l\'immobilisation prolongée de vos engins sur chantier.');
    }

    public function toArray($notifiable): array
    {
        return [
            'vehicle_id' => $this->vehicle->id,
            'plan_id' => $this->plan->id,
            'type' => 'preventative_due',
            'message' => "Maintenance préventive due pour {$this->vehicle->internal_code} ({$this->plan->name})",
        ];
    }
}
