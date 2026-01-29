<?php

namespace App\Notifications\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpirationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Tiers $tier) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('tiers.notifications.document_expiration_subject'))
            ->line(__('tiers.notifications.document_expiration_message', ['name' => $this->tier->display_name]));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tier_id' => $this->tier->id,
            'message' => __('notifications.tiers.document_expiration_message'),
        ];
    }
}
