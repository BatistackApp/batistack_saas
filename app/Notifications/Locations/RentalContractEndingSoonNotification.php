<?php

namespace App\Notifications\Locations;

use App\Models\Locations\RentalContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalContractEndingSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public RentalContract $contract)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Fin de location imminente : {$this->contract->reference}")
            ->line("Le contrat de location '{$this->contract->label}' pour le chantier {$this->contract->project->name} arrive à échéance.")
            ->line("Date de fin prévue : " . $this->contract->end_date_planned->format('d/m/Y'))
            // ->action('Gérer le contrat', url('/admin/locations/contracts/' . $this->contract->id))
            ->line('Pensez à confirmer le rendu du matériel ou à prolonger le contrat pour éviter les surcoûts.');
    }

    public function toArray($notifiable): array
    {
        return [
            'contract_id' => $this->contract->id,
            'reference' => $this->contract->reference,
            'end_date' => $this->contract->end_date_planned,
            'message' => "La location {$this->contract->reference} se termine dans 48h."
        ];
    }
}
