<?php

namespace App\Observers\Projects;

use App\Jobs\Projects\RecalculateProjectBudgetJob;
use App\Models\Projects\ProjectPhase;

class ProjectPhaseObserver
{
    /**
     * Handle the ProjectPhase "created" event.
     */
    public function created(ProjectPhase $projectPhase): void
    {
        $this->dispatchRecalculation($projectPhase);
    }

    /**
     * Handle the ProjectPhase "updated" event.
     */
    public function updated(ProjectPhase $projectPhase): void
    {
        $this->dispatchRecalculation($projectPhase);
    }

    /**
     * Handle the ProjectPhase "deleted" event.
     */
    public function deleted(ProjectPhase $projectPhase): void
    {
        $this->dispatchRecalculation($projectPhase);
    }

    /**
     * Handle the ProjectPhase "restored" event.
     */
    public function restored(ProjectPhase $projectPhase): void
    {
        $this->dispatchRecalculation($projectPhase);
    }

    /**
     * Handle the ProjectPhase "force deleted" event.
     */
    public function forceDeleted(ProjectPhase $projectPhase): void
    {
        $this->dispatchRecalculation($projectPhase);
    }

    /**
     * Dispatch the budget recalculation job safely.
     */
    protected function dispatchRecalculation(ProjectPhase $projectPhase): void
    {
        // On vérifie que le projet est bien accessible avant de lancer le job
        // pour éviter le TypeError si la relation est nulle.
        if ($projectPhase->project) {
            RecalculateProjectBudgetJob::dispatch($projectPhase->project);
        }
    }
}
