<?php

namespace App\Services\Projects;

use App\Enums\Projects\ProjectPhaseStatus;
use App\Enums\Projects\ProjectStatus;
use App\Enums\Tiers\TierStatus;
use App\Models\Projects\Project;
use DB;
use Exception;

class ProjectManagementService
{
    public function __construct()
    {
    }

    /**
     * Change le statut du projet en appliquant les règles métier (Etape 3).
     * @throws Exception
     */
    public function transitionToStatus(Project $project, ProjectStatus $newStatus): void
    {
        if ($project->customer && $project->customer->status === TierStatus::Suspended) {
            throw new Exception("Le client est suspendu ou non conforme");
        }

        try {
            $this->validateTransition($project, $newStatus);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        try {
            DB::transaction(function () use ($project, $newStatus) {
                // Logique spécifique par transition
                if ($newStatus === ProjectStatus::InProgress) {
                    $this->handleStartProject($project);
                }

                if ($newStatus === ProjectStatus::Finished) {
                    $this->handleFinishProject($project);
                }

                $project->update(['status' => $newStatus]);
            });
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } catch (\Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Vérifie la conformité du client lié (Module Tiers).
     */
    public function checkCustomerCompliance(Project $project): bool
    {
        $customer = $project->customer;

        if (!$customer) return false;

        // On bloque si le client est suspendu ou archivé
        return !in_array($customer->status, [
            TierStatus::Suspended,
            TierStatus::Archived
        ]);
    }

    /**
     * Validation des règles de clôture et de démarrage.
     * @throws Exception
     */
    private function validateTransition(Project $project, ProjectStatus $newStatus): void
    {
        if (in_array($newStatus, [ProjectStatus::InProgress->value, ProjectStatus::Study->value])) {
            if (!$this->checkCustomerCompliance($project)) {
                throw new Exception("Action impossible : Le client est suspendu ou non conforme.");
            }
        }

        if ($newStatus === ProjectStatus::Finished) {
            $hasActivePhases = $project->phases()
                ->where('status', ProjectPhaseStatus::InProgress)
                ->exists();

            if ($hasActivePhases) {
                throw new Exception("Impossible de terminer le chantier : certaines phases sont encore en cours.");
            }
        }
    }

    private function handleStartProject(Project $project): void
    {
        if (!$project->actual_start_at) {
            $project->actual_start_at = now();
        }

        // Passage automatique des phases initiales en "InProgress"
        $project->phases()
            ->where('order', 0)
            ->where('status', ProjectPhaseStatus::Pending)
            ->update(['status' => ProjectPhaseStatus::InProgress]);
    }

    private function handleFinishProject(Project $project): void
    {
        $project->actual_end_at = now();
    }
}
