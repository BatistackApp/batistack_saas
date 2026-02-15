<?php

namespace App\Services\Fleet;

use App\Enums\Fleet\DesignationStatus;
use App\Enums\Fleet\FinesStatus;
use App\Models\Fleet\VehicleAssignment;
use App\Models\Fleet\VehicleFine;
use App\Services\Accounting\AccountingEntryService;
use DB;
use Illuminate\Support\Collection;

class FineService
{
    public function __construct(protected AccountingEntryService $entryService){}

    /**
     * Tente de réconcilier une contravention avec un conducteur et un chantier.
     * Cette méthode est le coeur de l'automatisation.
     */
    public function autoReconcile(VehicleFine $fine): array
    {
        // 1. Recherche de l'affectation correspondante
        $assignment = VehicleAssignment::where('vehicle_id', $fine->vehicle_id)
            ->where('started_at', '<=', $fine->offense_at)
            ->where(function ($query) use ($fine) {
                $query->whereNull('ended_at')
                    ->orWhere('ended_at', '>=', $fine->offense_at);
            })
            ->first();

        $updates = [];
        $results = [
            'match_found' => false,
            'driver' => null,
            'project' => null
        ];

        if ($assignment) {
            $results['match_found'] = true;
            $results['driver'] = $assignment->user_id;
            $results['project'] = $assignment->project_id;

            // Mise à jour automatique de la contravention
            $fine->update([
                'user_id' => $assignment->user_id,
                'project_id' => $assignment->project_id,
                'status' => FinesStatus::DriverAssigned,
                'notes' => ($fine->notes ? $fine->notes . "\n" : "") . "Match automatique via affectation #" . $assignment->id
            ]);
        }

        return $results;
    }

    /**
     * Marque une contravention pour désignation ANTAI.
     */
    public function markForDesignation(VehicleFine $fine): bool
    {
        if (!$fine->user_id) {
            return false;
        }

        return $fine->update([
            'designation_status' => DesignationStatus::Pending,
            'status' => FinesStatus::DriverAssigned
        ]);
    }

    /**
     * Prépare les données pour l'export CSV ANTAI (Désignation en masse).
     * Format basé sur les spécifications de l'ANTAI pour les flottes.
     */
    public function formatForAntaiExport(Collection $fines): array
    {
        return $fines->map(function ($fine) {
            $driver = $fine->driver;

            return [
                'num_avis' => $fine->notice_number,
                'date_infraction' => $fine->offense_at->format('d/m/Y'),
                'immatriculation' => $fine->vehicle->license_plate,
                'nom_conducteur' => $driver?->nom,
                'prenom_conducteur' => $driver?->prenom,
                'email' => $driver?->email,
                'adresse' => $driver?->address, // Nécessite que le modèle User ait ces champs
                'date_naissance' => $driver?->birth_date,
                'num_permis' => $driver?->license_number,
            ];
        })->toArray();
    }

    /**
     * Statistiques du module pour le tableau de bord Flotte.
     */
    public function getStats(int $tenantId): array
    {
        return [
            'total_pending' => VehicleFine::where('tenants_id', $tenantId)
                ->where('status', FinesStatus::Received)
                ->count(),
            'total_amount_due' => VehicleFine::where('tenants_id', $tenantId)
                ->whereNotIn('status', [FinesStatus::Paid, FinesStatus::Archived])
                ->sum('amount_initial'),
            'critical_delays' => VehicleFine::where('tenants_id', $tenantId)
                ->where('due_date', '<', now()->addDays(5))
                ->whereNotIn('status', [FinesStatus::Paid, FinesStatus::Archived])
                ->count(),
        ];
    }

    /**
     * Gère le paiement d'une amende par l'entreprise (ex: stationnement).
     */
    public function recordPayment(VehicleFine $fine, float $amount, string $reference): bool
    {
        return DB::transaction(function () use ($fine, $amount, $reference) {
            $fine->update([
                'status' => FinesStatus::Paid,
                'notes' => $fine->notes . "\nPayé le " . now()->format('d/m/Y') . " - Réf: " . $reference
            ]);

            // Ici, on pourrait déclencher une écriture comptable si nécessaire
            return true;
        });
    }
}
