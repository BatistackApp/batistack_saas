<?php

namespace App\Notifications\Fleet;

use App\Models\Fleet\VehicleCheck;
use App\Models\Fleet\VehicleMaintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChecklistAnomalyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public VehicleCheck $check,
        public VehicleMaintenance $maintenance
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $vehicle = $this->check->vehicle;

        return (new MailMessage)
            ->error()
            ->subject("⚠️ Anomalie critique : {$vehicle->internal_code}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Une anomalie a été détectée sur le véhicule **{$vehicle->name}** ({$vehicle->license_plate}) lors du contrôle de " . ($this->check->type === 'start' ? 'prise de poste' : 'fin de journée') . ".")
            ->line("Un bon de travaux curatifs a été généré automatiquement (Réf: {$this->maintenance->internal_reference}).")
            ->action('Consulter la maintenance', url("/fleet/maintenances/{$this->maintenance->id}"))
            ->line('Merci de vérifier si l\'immobilisation du matériel est nécessaire.');
    }

    public function toDatabase($notifiable): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        return [
            'check_id' => $this->check->id,
            'vehicle_id' => $this->check->vehicle_id,
            'vehicle_code' => $this->check->vehicle->internal_code,
            'maintenance_id' => $this->maintenance->id,
            'message' => "Anomalie détectée sur {$this->check->vehicle->internal_code}",
        ];
    }
}
