<?php

namespace App\Notifications\Fleet;

use App\Models\Fleet\VehicleFine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FineAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected VehicleFine $fine) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Avis de contravention : Action requise')
            ->line("Une contravention a été identifiée pour le véhicule qu'on vous avait confié.")
            ->line("Immatriculation : {$this->fine->vehicle->license_plate}")
            ->line("Date de l'infraction : {$this->fine->offense_at->format('d/m/Y H:i')}")
            ->line('Nous allons procéder à votre désignation auprès des autorités (ANTAI).')
            // ->action('Voir le détail', url("/my-fleet/fines/{$this->fine->id}"))
            ->line('Merci de vérifier vos informations personnelles sur votre profil Batistack.');
    }

    public function toArray($notifiable): array
    {
        return [
            'fine_id' => $this->fine->id,
            'amount' => $this->fine->amount_initial,
            'message' => "Vous avez été désigné pour une contravention sur le véhicule {$this->fine->vehicle->license_plate}.",
        ];
    }
}
