<?php

namespace App\Notifications\HR;

use App\Models\HR\EmployeeTimesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimesheetSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private EmployeeTimesheet $timesheet)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('hr.notifications.timesheet_submitted_subject'))
            ->greeting(__('hr.notifications.hello', ['name' => $notifiable->name]))
            ->line(__('hr.notifications.timesheet_submitted_body', [
                'date' => $this->timesheet->timesheet_date->format('d/m/Y'),
            ]))
            ->action(__('hr.notifications.view_timesheet'), '#');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'timesheet_id' => $this->timesheet->id,
            'message' => __('hr.notifications.timesheet_submitted_body', [
                'date' => $this->timesheet->timesheet_date->format('d/m/Y'),
            ]),
        ];
    }
}
