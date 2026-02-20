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
        $totalSales = $project->totalSalesBudget();
        $costs = $this->calculateDetailedCosts($project);
        $totalSpent = array_sum($costs);

        $totalRad = $project->phases->sum(fn ($p) => $p->totalRad());
        $forecastFinalCost = $totalSpent + $totalRad;

        $physicalProgress = $this->calculateWeightedProgress($project);

        return [
            'sales_budget_total' => $totalSales,
            'internal_budget_total' => $project->totalInternalBudget(),
            'total_spent' => $totalSpent,
            'total_rad' => $totalRad,
            'forecast_final_cost' => $forecastFinalCost,
            'forecast_margin' => $totalSales - $forecastFinalCost,
            'progress_physical' => $physicalProgress,
            'health_index' => $physicalProgress - ($totalSales > 0 ? ($totalSpent / $totalSales) * 100 : 0),
            'allocated_budget' => $project->phases->sum('allocated_budget'),
            'actual_cost' => $totalSpent,
        ];
    }

    private function calculateDetailedCosts(Project $project): array
    {
        return [
            'labor' => 0, // Mock: Liaison Pointage
            'materials' => 0, // Mock: Liaison Achats
            'subcontracting' => 0, // Mock: Liaison Tiers (Sub)
            'rentals' => 0, // Mock: Liaison Flotte
            'overheads' => 0,
            'management' => 0, // Mock: Payroll
        ];
    }

    private function calculateWeightedProgress(Project $project): float
    {
        $totalInternal = $project->totalInternalBudget();

        if ($totalInternal <= 0) {
            return 0;
        }

        $weightedProgress = $project->phases->sum(function ($phase) {
            return ($phase->progress_percentage / 100) * $phase->allocated_budget;
        });

        return ($weightedProgress / $totalInternal) * 100;
    }
}
