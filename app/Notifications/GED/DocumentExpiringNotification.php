<?php

namespace App\Notifications\GED;

use App\Models\GED\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Document $document,
        public int $daysRemaining,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Alerte : Document arrivant à expiration')
            ->greeting('Bonjour,')
            ->line("Le document suivant arrive à expiration dans {$this->daysRemaining} jours :")
            ->line("**Nom :** {$this->document->name}")
            ->line("**Type :** {$this->document->type->getLabel()}")
            ->action('Voir le document', url("/ged/documents/{$this->document->id}"))
            ->line('Merci de prévoir son renouvellement pour rester en conformité.');
    }

    public function toArray($notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'document_name' => $this->document->name,
            'days_remaining' => $this->daysRemaining,
            'type' => 'expiration_warning'
        ];
    }
}
