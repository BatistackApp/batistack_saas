<?php

namespace App\Observers\Projects;

use App\Enums\Projects\ProjectAmendmentStatus;
use App\Jobs\Projects\RecalculateProjectBudgetJob;
use App\Models\Projects\ProjectAmendment;

class ProjectAmendmentObserver
{
    public function updated(ProjectAmendment $amendment): void
    {
        if ($amendment->wasChanged('status') && $amendment->status === ProjectAmendmentStatus::Accepted) {
            RecalculateProjectBudgetJob::dispatch($amendment->project);
        }
    }
}
