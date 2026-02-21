<?php

namespace App\Services\Locations;

use App\Enums\Locations\RentalStatus;
use App\Exceptions\Locations\RentalWorkflowException;
use App\Models\Locations\RentalAssignment;
use App\Models\Locations\RentalContract;
use App\Models\Projects\Project;
use DB;
use Illuminate\Support\Carbon;

/**
 * Orchestrateur du Workflow de location.
 */
class RentalWorkflowService
{
    public function __construct(protected RentalCostImputationService $imputationService) {}

    /**
     * Active une location (Livraison sur site).
     */
    public function startRental(RentalContract $contract, Carbon $actualDate): void
    {
        // Vérification de la conformité du Loueur (via ton modèle Tiers)
        if (! $contract->provider->isCompliant()) {
            throw new RentalWorkflowException("Le loueur n'est pas à jour de ses documents administratifs.");
        }

        DB::transaction(function () use ($contract, $actualDate) {
            $contract->update([
                'status' => RentalStatus::ACTIVE,
                'actual_pickup_at' => $actualDate,
            ]);

            // Création de l'affectation initiale
            RentalAssignment::create([
                'tenants_id' => $contract->tenants_id,
                'rental_contract_id' => $contract->id,
                'project_id' => $contract->project_id,
                'assigned_at' => $actualDate,
            ]);
        });
    }

    /**
     * Déclenche l'appel de reprise (Off-Hire).
     * Stoppe la facturation théorique.
     */
    public function requestOffHire(RentalContract $contract, Carbon $requestedAt): void
    {
        if ($contract->status !== RentalStatus::ACTIVE) {
            throw new RentalWorkflowException("Seul un contrat actif peut faire l'objet d'un appel de reprise.");
        }

        DB::transaction(function () use ($contract, $requestedAt) {
            // On impute les derniers coûts jusqu'à la date de demande
            $this->imputationService->imputeDailyCost($contract);

            $contract->update([
                'status' => RentalStatus::OFF_HIRE,
                'off_hire_requested_at' => $requestedAt,
            ]);

            // On clôture l'affectation chantier en cours
            $contract->assignments()->whereNull('released_at')->update([
                'released_at' => $requestedAt
            ]);
        });
    }

    /**
     * Transfert le matériel vers un nouveau projet (Axe 7).
     */
    public function transferToProject(RentalContract $contract, Project $newProject, Carbon $transferDate): void
    {
        if ($contract->status !== RentalStatus::ACTIVE) {
            throw new RentalWorkflowException("Impossible de transférer un matériel qui n'est pas en cours de location.");
        }

        DB::transaction(function () use ($contract, $newProject, $transferDate) {
            // 1. Clôturer l'affectation actuelle
            $contract->assignments()->whereNull('released_at')->update([
                'released_at' => $transferDate
            ]);

            // 2. Créer la nouvelle affectation
            RentalAssignment::create([
                'tenants_id' => $contract->tenants_id,
                'rental_contract_id' => $contract->id,
                'project_id' => $newProject->id,
                'assigned_at' => $transferDate,
            ]);
        });
    }

    /**
     * Termine une location (Retour matériel).
     */
    public function endRental(RentalContract $contract, Carbon $actualDate): void
    {
        if (!in_array($contract->status, [RentalStatus::ACTIVE, RentalStatus::OFF_HIRE])) {
            throw new RentalWorkflowException('Le contrat doit être actif ou en appel de reprise pour être terminé.');
        }

        DB::transaction(function () use ($contract, $actualDate) {
            // Si on n'était pas encore en Off-Hire, on clôture l'imputation
            if ($contract->status === RentalStatus::ACTIVE) {
                $this->imputationService->imputeDailyCost($contract);
                $contract->assignments()->whereNull('released_at')->update(['released_at' => $actualDate]);
            }

            // 2. Clôture du contrat
            $contract->update([
                'status' => RentalStatus::ENDED,
                'actual_return_at' => $actualDate,
            ]);
        });
    }
}
