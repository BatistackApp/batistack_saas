<?php

namespace App\Services\Intervention;

use App\Enums\HR\TimeEntryStatus;
use App\Enums\Intervention\InterventionStatus;
use App\Exceptions\Intervention\ComplianceException;
use App\Exceptions\Intervention\InvalidStatusTransitionException;
use App\Models\HR\TimeEntry;
use App\Models\Intervention\Intervention;
use App\Notifications\Intervention\InterventionCompletedNotification;
use App\Services\Projects\ProjectManagementService;
use Auth;
use DB;

class InterventionWorkflowService
{
    public function __construct(
        protected InterventionFinancialService $financialService,
        protected InterventionStockManager $stockManager,
        protected ProjectManagementService $projectService
    ) {}

    /**
     * Démarre l'intervention (Vérification conformité).
     */
    public function start(Intervention $intervention): void
    {
        if ($intervention->status !== InterventionStatus::Planned) {
            throw new InvalidStatusTransitionException("L'intervention doit être en statut 'Planifiée'.");
        }

        // Vérification conformité client via ProjectManagementService
        if ($intervention->project) {
            if (! $this->projectService->checkCustomerCompliance($intervention->project)) {
                throw new ComplianceException('Le client ne respecte pas les normes du projet. Le client est suspendu');
            }
        }

        $intervention->update(['status' => InterventionStatus::InProgress]);
    }

    /**
     * Clôture l'intervention (Déstockage + Imputation Heures + Calcul final).
     */
    public function complete(Intervention $intervention): void
    {
        if ($intervention->status !== InterventionStatus::InProgress) {
            throw new InvalidStatusTransitionException("L'intervention doit être 'En cours' pour être terminée.");
        }

        DB::transaction(function () use ($intervention) {
            // 1. Vérification et exécution des sorties de stock
            $this->stockManager->validateStockAvailability($intervention);
            $this->stockManager->processStockExits($intervention);

            // 2. Génération des TimeEntries pour les techniciens
            $this->generateTimeEntries($intervention);

            // 3. Calcul final de la marge
            $this->financialService->refreshValuation($intervention);

            $intervention->update(['status' => InterventionStatus::Completed]);

            if (Auth::user()) {
                Auth::user()->notify(new InterventionCompletedNotification($intervention));
            }
        });
    }

    /**
     * Génère les pointages RH à partir des techniciens de l'intervention.
     */
    protected function generateTimeEntries(Intervention $intervention): void
    {
        foreach ($intervention->technicians as $employee) {
            TimeEntry::create([
                'tenants_id' => $intervention->tenants_id,
                'employee_id' => $employee->id,
                'project_id' => $intervention->project_id,
                'project_phase_id' => $intervention->project_phase_id,
                'date' => $intervention->planned_at?->toDateString() ?? now()->toDateString(),
                'hours' => $employee->pivot->hours_spent,
                'status' => TimeEntryStatus::Submitted,
                'notes' => "Intervention : {$intervention->reference} - {$intervention->label}",
            ]);
        }
    }
}
