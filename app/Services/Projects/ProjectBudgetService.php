<?php

namespace App\Services\Projects;

use App\Models\Projects\Project;

class ProjectBudgetService
{
    public function __construct()
    {
    }

    /**
     * Calcule la synthèse financière complète d'un projet.
     * Inclut le total dépensé, le reste à dépenser (RAD) et la marge.
     */
    public function getFinancialSummary(Project $project): array
    {
        // Dans une implémentation réelle, ces totaux viendraient des modules :
        // Pointage (Heures), Commerce (Achats/Factures) et Flottes.
        // Pour l'instant, nous préparons la structure.

        $totalAllocated = $project->phases()->sum('allocated_budget');
        $actualCosts = $this->calculateActualCosts($project);

        $rad = max(0, $totalAllocated - $actualCosts);
        $margin = $project->initial_budget_ht - $actualCosts;

        return [
            'initial_budget' => $project->initial_budget_ht,
            'allocated_budget' => $totalAllocated,
            'actual_costs' => $actualCosts,
            'remaining_budget' => $rad, // RAD
            'margin' => $margin,
            'health_index' => $this->calculateHealthIndex($project, $actualCosts),
        ];
    }

    /**
     * Calcule les coûts réels agrégés (Main d'oeuvre + Achats + Locations).
     */
    private function calculateActualCosts(Project $project): float
    {
        // TODO: Jointures avec les tables de pointage et factures fournisseurs
        // return $project->laborCosts()->sum('total') + $project->purchaseCosts()->sum('total');
        return 0.0; // Placeholder
    }

    /**
     * Calcule l'indice de santé (Avancement budgétaire vs Avancement physique).
     */
    private function calculateHealthIndex(Project $project, float $actualCosts): float
    {
        if ($project->initial_budget_ht <= 0) return 0;

        $budgetConsumptionRatio = ($actualCosts / $project->initial_budget_ht) * 100;
        // L'avancement physique sera calculé via une moyenne pondérée des phases.
        $physicalProgress = $project->phases()->avg('progress_percentage') ?? 0;

        return $physicalProgress - $budgetConsumptionRatio;
    }
}
