<?php

namespace App\Services\Projects;

use App\Models\Projects\Project;

class ProjectBudgetService
{
    /**
     * Calcule la synthèse financière complète d'un projet.
     * Inclut le total dépensé, le reste à dépenser (RAD) et la marge.
     */
    public function getFinancialSummary(Project $project): array
    {
        // Dans une implémentation réelle, ces totaux viendraient des modules :
        // Pointage (Heures), Commerce (Achats/Factures) et Flottes.
        // Pour l'instant, nous préparons la structure.

        $costs = $this->calculateDetailedCosts($project);
        $internalBudget = $project->internal_target_budget_ht;

        $totalSpent = array_sum($costs);
        $physicalProgress = $this->calculateWeightedProgress($project);

        return [
            'sales_budget' => $project->initial_budget_ht,
            'internal_budget' => $internalBudget,
            'costs_breakdown' => $costs,
            'total_spent' => $totalSpent,
            'progress_physical' => $physicalProgress,
            'health_index' => $physicalProgress - ($internalBudget > 0 ? ($totalSpent / $internalBudget) * 100 : 0),
            'margin_projection' => $project->initial_budget_ht - $totalSpent
        ];
    }

    private function calculateDetailedCosts(Project $project): array {
        return [
            'labor' => 0, // Mock: Liaison Pointage
            'materials' => 0, // Mock: Liaison Achats
            'subcontracting' => 0, // Mock: Liaison Tiers (Sub)
            'rentals' => 0 // Mock: Liaison Flotte
        ];
    }

    private function calculateWeightedProgress(Project $project): float {
        $totalBudget = $project->phases()->sum('allocated_budget');
        if ($totalBudget <= 0) return 0;

        $weightedProgress = $project->phases->sum(function ($phase) {
            return ($phase->progress_percentage / 100) * $phase->allocated_budget;
        });

        return ($weightedProgress / $totalBudget) * 100;
    }
}
