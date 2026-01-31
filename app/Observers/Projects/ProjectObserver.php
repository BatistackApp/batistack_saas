<?php

namespace App\Observers\Projects;

use App\Enums\Projects\ProjectStatus;
use App\Jobs\Projects\InitializeProjectProcurementJob;
use App\Jobs\Projects\RecalculateProjectBudgetJob;
use App\Jobs\Projects\TriggerProjectPlanningJob;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectStatusHistory;
use App\Notifications\Projects\ProjectSuspendedNotification;
use Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProjectObserver
{
    public function creating(Project $project): void
    {
        if (empty($project->code_project)) {
            $year = now()->year;
            $random = strtoupper(Str::random(4));
            $project->code_project = "CH-{$year}-{$random}";
        }
    }

    /**
     * Gère la validation avant la mise à jour en base de données.
     * * @throws ValidationException
     */
    public function updating(Project $project): void
    {
        // Recommandation : Forcer la raison de suspension
        if ($project->isDirty('status') && $project->status === ProjectStatus::Suspended) {
            if (empty($project->suspension_reason)) {
                throw ValidationException::withMessages([
                    'suspension_reason' => 'Action bloquée : Un motif de suspension doit obligatoirement être renseigné pour mettre le chantier à l\'arrêt.',
                ]);
            }
        }
    }

    public function updated(Project $project): void
    {
        if ($project->wasChanged(['initial_budget_ht', 'status'])) {
            RecalculateProjectBudgetJob::dispatch($project);
        }

        // Si le statut a bien été changé en "Suspendu"
        if ($project->wasChanged('status') && $project->status === ProjectStatus::Suspended) {
            $this->notifyManagement($project);
        }

        if ($project->wasChanged('status')) {
            ProjectStatusHistory::create([
                'project_id' => $project->id,
                'old_status' => $project->getOriginal('status'),
                'new_status' => $project->status,
                'changed_by_user_id' => Auth::id(),
                'reason' => $project->status === ProjectStatus::Suspended ? $project->suspension_reason->value : null,
                'changed_at' => now(),
            ]);
            // Notification spécifique pour le statut 'Accepted'
            if ($project->status === ProjectStatus::Accepted) {
                // Notifier Achats & Planification
                dispatch(new TriggerProjectPlanningJob($project));
                dispatch(new InitializeProjectProcurementJob($project));
            }

            // Si suspendu, vérifier que la raison est renseignée
            if ($project->status === ProjectStatus::Suspended && !$project->suspension_reason) {
                // Logique d'alerte ou rollback
            }
        }
    }

    /**
     * Envoie une notification urgente aux responsables du projet.
     */
    protected function notifyManagement(Project $project): void
    {
        // On récupère les conducteurs de travaux et les admins liés
        $managers = $project->members()
            ->wherePivot('role', \App\Enums\Projects\ProjectUserRole::ProjectManager->value)
            ->get();

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new ProjectSuspendedNotification($project));
        }
    }
}
