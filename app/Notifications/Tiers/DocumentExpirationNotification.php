<?php

namespace App\Notifications\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class DocumentExpirationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Tiers $tier, private Collection $expiringDocs) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('tiers.notifications.document_expiration_subject'))
            ->line(__('tiers.notifications.document_expiration_message', ['name' => $this->tier->display_name]));

        foreach ($this->expiringDocs as $doc) {
            $mail->line(" - {$doc->type}: expire le {$doc->expires_at->format('d/m/Y')}");
        }

        return $mail;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tier_id' => $this->tier->id,
            'message' => __('notifications.tiers.document_expiration_message'),
        ];
    }
}
