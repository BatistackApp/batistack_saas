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
    public function requestOffHire(RentalContract $contract, Carbon $requestedAt, ?string $reference = null): void
    {
        if ($contract->status !== RentalStatus::ACTIVE) {
            throw new RentalWorkflowException("Seul un contrat actif peut faire l'objet d'un appel de reprise.");
        }

        DB::transaction(function () use ($contract, $requestedAt, $reference) {
            // On impute les derniers coûts jusqu'à la date de demande
            $this->imputationService->imputeDailyCost($contract);

            $contract->update([
                'status' => RentalStatus::OFF_HIRE,
                'off_hire_requested_at' => $requestedAt,
                'notes' => trim($contract->notes."\nArrêt demandé le ".$requestedAt->format('d/m/Y')." (Réf: $reference)."),
            ]);

            // On clôture l'affectation chantier en cours
            $contract->assignments()->whereNull('released_at')->update([
                'released_at' => $requestedAt,
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
                'released_at' => $transferDate,
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
        if (! in_array($contract->status, [RentalStatus::ACTIVE, RentalStatus::OFF_HIRE])) {
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

    /**
     * Étape 3 : Confirmation du retour physique (Reprise par le loueur).
     * Clôture définitive du dossier et dernière imputation des coûts.
     */
    public function confirmReturn(RentalContract $contract, Carbon $actualReturnDate): void
    {
        // On peut terminer depuis ACTIVE (retour direct) ou OFF_HIRE (retour après appel)
        if (! in_array($contract->status, [RentalStatus::ACTIVE, RentalStatus::OFF_HIRE])) {
            throw new RentalWorkflowException("Le contrat n'est pas dans un état permettant la clôture.");
        }

        DB::transaction(function () use ($contract, $actualReturnDate) {
            // 1. On effectue la dernière imputation analytique pour le projet
            // Le service d'imputation utilisera off_hire_requested_at si présent pour borner le calcul
            $this->imputationService->imputeDailyCost($contract);

            // 2. Mise à jour finale du contrat
            $contract->update([
                'status' => RentalStatus::ENDED,
                'actual_return_at' => $actualReturnDate,
            ]);
        });
    }

    /**
     * Annule un contrat (uniquement si en brouillon).
     */
    public function cancelContract(RentalContract $contract): void
    {
        if ($contract->status !== RentalStatus::DRAFT) {
            throw new RentalWorkflowException("Impossible d'annuler un contrat déjà activé.");
        }

        $contract->update(['status' => RentalStatus::CANCELLED]);
    }
}
