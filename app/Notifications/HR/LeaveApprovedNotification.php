<?php

namespace App\Notifications\HR;

use App\Models\HR\EmployeeLeave;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private EmployeeLeave $leave)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('hr.notifications.leave_approved_subject'))
            ->greeting(__('hr.notifications.hello', ['name' => $notifiable->name]))
            ->line(__('hr.notifications.leave_approved_body', [
                'start_date' => $this->leave->start_date->format('d/m/Y'),
                'end_date' => $this->leave->end_date->format('d/m/Y'),
            ]));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'leave_id' => $this->leave->id,
            'message' => __('hr.notifications.leave_approved_body', [
                'start_date' => $this->leave->start_date->format('d/m/Y'),
                'end_date' => $this->leave->end_date->format('d/m/Y'),
            ]),
        ];
    }
}
