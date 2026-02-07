<?php

namespace App\Services\Locations;

use App\Enums\Locations\RentalStatus;
use App\Exceptions\Locations\RentalWorkflowException;
use App\Models\Locations\RentalContract;
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
        if (!$contract->provider->isCompliant()) {
            throw new RentalWorkflowException("Le loueur n'est pas à jour de ses documents administratifs.");
        }

        $contract->update([
            'status' => RentalStatus::ACTIVE,
            'actual_pickup_at' => $actualDate
        ]);
    }

    /**
     * Termine une location (Retour matériel).
     */
    public function endRental(RentalContract $contract, Carbon $actualDate): void
    {
        if ($contract->status !== RentalStatus::ACTIVE) {
            throw new RentalWorkflowException("Seul un contrat actif peut être terminé.");
        }

        DB::transaction(function () use ($contract, $actualDate) {
            // 1. On effectue une dernière imputation pour la période résiduelle
            $this->imputationService->imputeDailyCost($contract);

            // 2. Clôture du contrat
            $contract->update([
                'status' => RentalStatus::ENDED,
                'actual_return_at' => $actualDate
            ]);
        });
    }
}
