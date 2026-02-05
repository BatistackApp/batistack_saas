<?php

namespace App\Services\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleToll;
use App\Services\UlysApiService;

class FleetTollService
{
    public function __construct(protected UlysApiService $apiService) {}

    /**
     * Orchestre la synchronisation des péages.
     */
    public function syncFromExternalSource(Vehicle $vehicle): int
    {
        if (! $vehicle->external_toll_tag_id) {
            return 0;
        }

        $trips = $this->apiService->fetchTollTrips($vehicle->external_toll_tag_id);

        $count = 0;
        foreach ($trips as $trip) {
            $this->recordToll($vehicle, [
                'entry_at' => $trip['entry_time'],
                'exit_at' => $trip['exit_time'],
                'entry_station' => $trip['entry_station'],
                'exit_station' => $trip['exit_station'],
                'amount_ht' => $trip['amount_ht'],
                'external_id' => $trip['id'],
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Enregistre un péage et tente l'imputation analytique automatique.
     */
    public function recordToll(Vehicle $vehicle, array $data): \Illuminate\Database\Eloquent\Model
    {
        // On cherche le chantier actif au moment de la sortie d'autoroute (exit_at)
        $assignment = $vehicle->assignments()
            ->where('started_at', '<=', $data['exit_at'])
            ->where(function ($q) use ($data) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', $data['exit_at']);
            })
            ->first();

        return VehicleToll::updateOrCreate(
            ['external_transaction_id' => $data['external_id'], 'vehicle_id' => $vehicle->id],
            [
                'project_id' => $assignment?->project_id, // Imputation auto !
                'entry_at' => $data['entry_at'],
                'exit_at' => $data['exit_at'],
                'entry_station' => $data['entry_station'],
                'exit_station' => $data['exit_station'],
                'amount_ht' => $data['amount_ht'],
            ]
        );
    }
}
