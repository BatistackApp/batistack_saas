<?php

namespace App\Notifications\Accounting;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FecExportReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $filePath,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre export FEC est prêt')
            ->greeting('Bonjour,')
            ->line('L\'export FEC que vous avez demandé est maintenant prêt à télécharger.')
            // ->action('Télécharger', route('accounting.fec.download', $this->filePath))
            ->line('Le fichier sera conservé pendant 7 jours.');
    }

    public function toDatabase($notifiable): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Export FEC prêt',
            'message' => 'Votre fichier FEC est disponible au téléchargement',
            'file_path' => $this->filePath,
        ];
    }
}
