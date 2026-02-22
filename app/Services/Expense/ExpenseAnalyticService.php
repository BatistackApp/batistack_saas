<?php

namespace App\Services\Expense;

use App\Models\Projects\ProjectPhase;

class ExpenseAnalyticService
{
    /**
     * Récupère les phases disponibles pour un projet donné.
     * Utile pour le filtrage dynamique dans l'API/Interface.
     */
    public function getPhasesForProject(int $projectId): ProjectPhase
    {
        return ProjectPhase::where('project_id', $projectId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code']);
    }

    /**
     * Valide si l'imputation choisie est cohérente.
     */
    public function validateImputation(int $projectId, ?int $phaseId): bool
    {
        if (! $phaseId) {
            return true;
        }

        return ProjectPhase::where('id', $phaseId)
            ->where('project_id', $projectId)
            ->exists();
    }

    /**
     * Suggère une phase par défaut si aucune n'est sélectionnée.
     * Souvent "Installation de chantier" ou "Général".
     */
    public function getDefaultPhase(int $projectId): ?int
    {
        return ProjectPhase::where('project_id', $projectId)
            ->where('is_default', true)
            ->value('id')->id;
    }
}
