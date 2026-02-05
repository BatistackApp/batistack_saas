<?php

namespace App\Notifications\HR;

use App\Models\HR\TimeEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TimeEntryApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TimeEntry $timeEntry) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Vos heures du '.$this->timeEntry->date->format('d/m/Y').' ont été approuvées.',
            'status' => 'approved',
        ];
    }
}
