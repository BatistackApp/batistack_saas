<?php

namespace App\Notifications\Projects;

use App\Models\Projects\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetThresholdReachedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Project $project,
        protected float $consumptionPercentage
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $percentage = number_format($this->consumptionPercentage, 1);

        return (new MailMessage)
            ->subject("⚠️ Alerte Budget : Chantier {$this->project->code_project}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le budget alloué pour le chantier **{$this->project->name}** est consommé à **{$percentage}%**.")
            ->line("Il est conseillé de vérifier les coûts réels et l'avancement des phases pour éviter tout dépassement.")
            ->error();
    }

    public function toArray($notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'project_code' => $this->project->code_project,
            'consumption' => $this->consumptionPercentage,
            'message' => "Seuil budgétaire atteint ({$this->consumptionPercentage}%)",
        ];
    }
}
