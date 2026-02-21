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

    public function __construct(
        public RentalContract $contract,
        public string $type = 'ending_soon',
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = $this->type === 'ending_soon'
            ? "Fin de location imminente : {$this->contract->reference}"
            : "Matériel en attente de reprise : {$this->contract->reference}";

        $message = (new MailMessage)
            ->subject($subject);

        if ($this->type === 'ending_soon') {
            $message->line("Le contrat de location '{$this->contract->label}' pour le chantier {$this->contract->project->name} arrive à échéance.")
                ->line('Date de fin prévue : ' . $this->contract->end_date_planned->format('d/m/Y'))
                ->line('Pensez à confirmer le rendu du matériel ou à prolonger le contrat pour éviter les surcoûts.');
        } else {
            $message->line("Alerte : Le matériel du contrat {$this->contract->reference} est en 'Off-Hire' depuis le " . $this->contract->off_hire_requested_at->format('d/m/Y') . ".")
                ->line("Le loueur n'a toujours pas confirmé la reprise physique sur le chantier : {$this->contract->project->name}.")
                ->line("Pensez à relancer le loueur pour libérer l'espace sur site.");
        }

        return $message;
    }

    public function toArray($notifiable): array
    {
        return [
            'contract_id' => $this->contract->id,
            'reference' => $this->contract->reference,
            'type' => $this->type,
            'message' => $this->type === 'ending_soon'
                ? "La location {$this->contract->reference} se termine bientôt."
                : "Le loueur tarde à récupérer le matériel {$this->contract->reference}.",
        ];
    }
}
