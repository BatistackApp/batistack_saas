<?php

namespace App\Notifications\Commerce;

use App\Models\Commerce\Reglement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReglementReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Reglement $reglement)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Paiement reçu : {$this->reglement->facture->number}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Un paiement de {$this->reglement->montant}€ a été reçu pour la facture #{$this->reglement->facture->number}.")
            //->action('Voir la facture', route('facture.show', $this->reglement->facture))
            ->line('Merci !');
    }
}
