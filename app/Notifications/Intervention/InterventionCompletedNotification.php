<?php

namespace App\Notifications\Intervention;

use App\Models\Intervention\Intervention;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterventionCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Intervention $intervention) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Intervention clôturée : {$this->intervention->reference}")
            ->line("L'intervention '{$this->intervention->label}' a été marquée comme terminée.")
            ->line("Détails financiers :")
            ->line("- Vente HT : " . number_format($this->intervention->amount_ht, 2) . " €")
            ->line("- Coût Matériel : " . number_format($this->intervention->material_cost_ht, 2) . " €")
            ->line("- Coût Main d'œuvre : " . number_format($this->intervention->labor_cost_ht, 2) . " €")
            ->line("- Marge générée : " . number_format($this->intervention->margin_ht, 2) . " €")
            ->action('Voir le rapport d\'intervention', url('/interventions/' . $this->intervention->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'intervention_id' => $this->intervention->id,
            'reference' => $this->intervention->reference,
            'message' => "L'intervention {$this->intervention->reference} a été clôturée avec succès.",
            'margin_ht' => $this->intervention->margin_ht,
            'total_ht' => $this->intervention->amount_ht,
        ];
    }
}
