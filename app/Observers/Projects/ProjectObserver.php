<?php

namespace App\Observers\Projects;

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
    }
}
