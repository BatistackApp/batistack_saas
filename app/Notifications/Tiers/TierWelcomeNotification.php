<?php

namespace App\Notifications\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TierWelcomeNotification extends Notification implements ShouldQueue
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
            ->subject(__('tiers.notifications.welcome_subject'))
            ->greeting(__('tiers.notifications.welcome_greeting', ['name' => $this->tier->display_name, 'type' => $this->tier->types->first()->type->value]))
            ->line(__('tiers.notifications.welcome_message'));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tier_id' => $this->tier->id,
            'tier_name' => $this->tier->display_name,
            'message' => __('notifications.tiers.welcome_message'),
        ];
    }
}
