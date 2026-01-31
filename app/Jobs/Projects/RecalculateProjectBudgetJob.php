<?php

namespace App\Jobs\Projects;

use App\Models\Projects\Project;
use App\Notifications\Projects\BudgetThresholdReachedNotification;
use App\Services\Projects\ProjectBudgetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class RecalculateProjectBudgetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Project $project)
    {
    }

    public function handle(ProjectBudgetService $budgetService): void
    {
        $summary = $budgetService->getFinancialSummary($this->project);

        // Si le coût réel dépasse 90% du budget alloué, on notifie le CT.
        if ($summary['allocated_budget'] > 0) {
            $consumption = ($summary['actual_costs'] / $summary['allocated_budget']) * 100;

            if ($consumption >= 90) {
                // On récupère le conducteur de travaux (CT) affecté
                // Proposition si plusieurs managers sont possibles
                $managers = $this->project->members()
                    ->wherePivot('role', \App\Enums\Projects\ProjectUserRole::ProjectManager->value)
                    ->get();

                if ($managers->isNotEmpty()) {
                    Notification::send($managers, new BudgetThresholdReachedNotification($this->project, $consumption));
                }
            }
        }
    }
}
