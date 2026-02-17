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

    public function __construct(private Project $project) {}

    public function handle(ProjectBudgetService $budgetService): void
    {
        $summary = $budgetService->getFinancialSummary($this->project);

        // Sécurisation : on s'assure que les clés existent, sinon on utilise 0 par défaut
        // Cela évite l'erreur "Undefined array key" lors des tests sur des projets vides
        $allocatedBudget = (float) ($summary['allocated_budget'] ?? 0);
        $actualCosts = (float) ($summary['actual_costs'] ?? 0);

        // Si le budget alloué est défini, on vérifie la consommation
        if ($allocatedBudget > 0) {
            $consumption = ($actualCosts / $allocatedBudget) * 100;

            // Si le coût réel dépasse 90% du budget alloué, on notifie le CT.
            if ($consumption >= 90) {
                // On récupère les membres ayant le rôle de Conducteur de Travaux (ProjectManager)
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
