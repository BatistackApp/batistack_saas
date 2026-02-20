<?php

namespace App\Notifications\Projects;

use App\Models\Projects\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectSuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Project $project) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("🚨 URGENT : Chantier Suspendu - {$this->project->code_project}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le chantier **{$this->project->name}** a été mis à l'arrêt (Statut Suspendu).")
            ->line('---')
            ->line('**Motif invoqué :**')
            ->line($this->project->suspension_reason->getLabel()) // Utilise le label de l'Enum
            ->line('---')
            ->action('Consulter le Dossier Chantier', url("/admin/projects/{$this->project->id}"))
            ->line('Veuillez prendre les mesures nécessaires auprès des équipes et du client.');
    }

    public function toArray($notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'project_code' => $this->project->code_project,
            'reason' => $this->project->suspension_reason->value,
            'reason_label' => $this->project->suspension_reason->getLabel(),
            'message' => "Le chantier {$this->project->code_project} est suspendu.",
        ];
    }
}
