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
     * Le technicien démarre son trajet vers le site.
     */
    public function startRoute(Intervention $intervention): void
    {
        $this->validateTransition($intervention, InterventionStatus::OnRoute);

        $intervention->update([
            'status' => InterventionStatus::OnRoute,
            'started_at' => now(), // On track le début de la mission globale
        ]);
    }

    /**
     * Le technicien arrive sur site et commence le travail.
     */
    public function arriveOnSite(Intervention $intervention): void
    {
        $this->validateTransition($intervention, InterventionStatus::InProgress);

        // Vérification de conformité client (bloquant au démarrage réel)
        if ($intervention->project && ! $this->projectService->checkCustomerCompliance($intervention->project)) {
            throw new ComplianceException('Client non conforme ou suspendu. Intervention bloquée.');
        }

        $intervention->update(['status' => InterventionStatus::InProgress]);
    }

    /**
     * Met l'intervention en attente (Pièce manquante, accès impossible).
     */
    public function putOnHold(Intervention $intervention, string $reason): void
    {
        $this->validateTransition($intervention, InterventionStatus::OnHold);

        $intervention->update([
            'status' => InterventionStatus::OnHold,
            'report_notes' => $intervention->report_notes."\n[MISE EN ATTENTE ".now()->format('d/m/Y H:i').'] : '.$reason,
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

        $this->validateTransition($intervention, InterventionStatus::Completed);

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
     * Logique de validation des transitions d'états.
     */
    protected function validateTransition(Intervention $intervention, InterventionStatus $targetStatus): void
    {
        $allowed = match ($targetStatus) {
            InterventionStatus::OnRoute => $intervention->status === InterventionStatus::Planned,
            InterventionStatus::InProgress => in_array($intervention->status, [InterventionStatus::OnRoute, InterventionStatus::OnHold]),
            InterventionStatus::OnHold => $intervention->status === InterventionStatus::InProgress,
            InterventionStatus::Completed => in_array($intervention->status, [InterventionStatus::InProgress, InterventionStatus::OnHold]),
            default => false
        };

        if (! $allowed) {
            throw new InvalidStatusTransitionException(
                "Transition impossible de {$intervention->status->value} vers {$targetStatus->value}"
            );
        }
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
                'notes' => "Rapport d'intervention [{$intervention->reference}] : ".substr($intervention->report_notes, 0, 100),
            ]);
        }
    }
}
