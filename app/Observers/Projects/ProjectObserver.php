<?php

namespace App\Observers\Projects;

use App\Enums\Projects\ProjectStatus;
use App\Jobs\Projects\RecalculateProjectBudgetJob;
use App\Models\Projects\Project;
use Illuminate\Support\Str;

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

    public function updated(Project $project): void
    {
        if ($project->wasChanged(['initial_budget_ht', 'status'])) {
            RecalculateProjectBudgetJob::dispatch($project);
        }

        if ($project->wasChanged('status')) {
            // Notification spécifique pour le statut 'Accepted'
            if ($project->status === ProjectStatus::Accepted) {
                // Notifier Achats & Planification
            }

            // Si suspendu, vérifier que la raison est renseignée
            if ($project->status === ProjectStatus::Suspended && !$project->suspension_reason) {
                // Logique d'alerte ou rollback
            }
        }
    }
}
