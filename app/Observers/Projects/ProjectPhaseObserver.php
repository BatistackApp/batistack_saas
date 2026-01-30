<?php

namespace App\Observers\Projects;

use App\Jobs\Projects\RecalculateProjectBudgetJob;
use App\Models\Projects\ProjectPhase;

class ProjectPhaseObserver
{
    public function saved(ProjectPhase $phase): void
    {
        if ($phase->wasChanged('allocated_budget')) {
            RecalculateProjectBudgetJob::dispatch($phase->project);
        }
    }
}
