<?php

namespace App\Notifications\HR;

use App\Models\HR\EmployeeSkill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SkillExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected EmployeeSkill $employeeSkill,
        protected int $daysRemaining
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $employee = $this->employeeSkill->employee;
        $skill = $this->employeeSkill->skill;

        return (new MailMessage)
            ->subject("Alerte Expiration : {$skill->name} - {$employee->full_name}")
            ->greeting('Bonjour,')
            ->line("La certification suivante arrive à échéance dans {$this->daysRemaining} jours :")
            ->line("**Collaborateur :** {$employee->full_name}")
            ->line("**Compétence :** {$skill->name}")
            ->line("**Date d'expiration :** ".$this->employeeSkill->expiry_date->format('d/m/Y'))
            ->action('Gérer le renouvellement', url('/hr/employees/'.$employee->id.'/skills'))
            ->line('Merci de prendre les dispositions nécessaires pour le renouvellement.');
    }

    public function toArray($notifiable): array
    {
        return [
            'employee_id' => $this->employeeSkill->employee_id,
            'employee_name' => $this->employeeSkill->employee->full_name,
            'skill_name' => $this->employeeSkill->skill->name,
            'expiry_date' => $this->employeeSkill->expiry_date->format('Y-m-d'),
            'days_remaining' => $this->daysRemaining,
            'type' => 'skill_expiration_alert',
        ];
    }
}
