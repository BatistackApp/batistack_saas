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
use Illuminate\Validation\ValidationException;

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
            throw new InvalidStatusTransitionException("L'intervention doit être 'Planifiée' pour être démarrée.");
        }

        // Vérification de conformité client
        if ($intervention->project && ! $this->projectService->checkCustomerCompliance($intervention->project)) {
            throw new ComplianceException('Client non conforme ou suspendu administrativement.');
        }

        // MODIFICATION : On enregistre l'heure exacte de début
        $intervention->update([
            'status' => InterventionStatus::InProgress,
            'started_at' => now(),
        ]);
    }

    /**
     * Clôture l'intervention (Déstockage + Imputation Heures + Calcul final).
     */
    public function complete(Intervention $intervention, array $reportData = []): void
    {
        if ($intervention->status !== InterventionStatus::InProgress) {
            throw new InvalidStatusTransitionException("L'intervention doit être 'En cours' pour être clôturée.");
        }

        // MODIFICATION : Validation stricte de la signature client (preuve juridique)
        if (empty($reportData['client_signature'])) {
            throw new ComplianceException("La signature du client est obligatoire pour valider le bon d'attachement.");
        }

        $intervention = $intervention->load('technicians');

        DB::transaction(function () use ($intervention, $reportData) {

            // 1. Mise à jour des données du rapport et du statut
            // MODIFICATION : On enregistre les notes et l'heure de fin
            $intervention->update(array_merge($reportData, [
                'status' => InterventionStatus::Completed,
                'completed_at' => now(),
            ]));

            // 2. Gestion des Stocks
            $this->stockManager->validateStockAvailability($intervention);
            $this->stockManager->processStockExits($intervention);

            // 3. Génération des pointages RH
            $this->generateTimeEntries($intervention);

            // 4. Calcul final de la rentabilité (via le service financier mis à jour)
            $this->financialService->refreshValuation($intervention);

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
                'date' => $intervention->completed_at?->toDateString() ?? now()->toDateString(),
                'hours' => $employee->pivot->hours_spent,
                'status' => TimeEntryStatus::Submitted,
                'notes' => "Rapport d'intervention [{$intervention->reference}] : " . substr($intervention->report_notes, 0, 100),
            ]);
        }
    }
}
