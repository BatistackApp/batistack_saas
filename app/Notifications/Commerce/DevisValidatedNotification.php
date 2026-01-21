<?php

namespace App\Notifications\Commerce;

use App\Models\Commerce\Devis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DevisValidatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Devis $devis)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Devis validé : {$this->devis->number}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le devis #{$this->devis->number} a été validé.")
            ->line('Merci !');
    }
}
