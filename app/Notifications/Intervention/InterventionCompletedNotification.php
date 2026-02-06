<?php

namespace App\Notifications\Intervention;

use App\Models\Intervention\Intervention;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InterventionCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Intervention $intervention) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'intervention_id' => $this->intervention->id,
            'message' => "L'intervention {$this->intervention->reference} a été clôturée avec succès.",
            'total_ht' => $this->intervention->amount_ht
        ];
    }
}
