<?php

namespace App\Notifications\Tiers;

use App\Enums\Tiers\TierStatus;
use App\Models\Tiers\Tiers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TierStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Tiers $tier, private TierStatus $previousStatus) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('tiers.notifications.status_changed_subject'))
            ->line(__('tiers.notifications.status_changed_message', [
                'tier' => $this->tier->display_name,
                'old_status' => $this->previousStatus->getLabel(),
                'new_status' => $this->tier->status->getLabel(),
            ]));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tier_id' => $this->tier->id,
            'previous_status' => $this->previousStatus->value,
            'new_status' => $this->tier->status->value,
        ];
    }
}
