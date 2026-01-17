<?php

namespace App\Notifications\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TierCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Tiers $tier)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        return [
            'tier_id' => $this->tier->id,
            'tier_name' => $this->tier->name,
            'message' => "Un nouveau tiers '{$this->tier->name}' a été créé.",
        ];
    }
}
