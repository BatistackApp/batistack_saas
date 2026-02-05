<?php

namespace App\Services\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleConsumption;
use App\Services\FuelCardApiService;
use Illuminate\Support\Facades\DB;

class FleetConsumptionService
{
    public function __construct(protected FuelCardApiService $apiService) {}

    /**
     * Orchestre la synchronisation pour un véhicule spécifique.
     */
    public function syncFromExternalSource(Vehicle $vehicle): int
    {
        if (! $vehicle->external_fuel_card_id) {
            return 0;
        }

        $since = $vehicle->last_external_sync_at?->format('Y-m-d');
        $transactions = $this->apiService->fetchTransactions($vehicle->external_fuel_card_id, $since);

        $count = 0;
        foreach ($transactions as $tx) {
            $this->recordFuelConsumption($vehicle, [
                'date' => $tx['date'],
                'quantity' => $tx['liters'],
                'amount_ht' => $tx['price_ht'],
                'odometer_reading' => $tx['odometer'],
                'source' => 'api_fuel_card',
                'external_transaction_id' => $tx['id'],
            ]);
            $count++;
        }

        if ($count > 0) {
            $vehicle->update(['last_external_sync_at' => now()]);
        }

        return $count;
    }

    /**
     * Enregistre une ligne de consommation et met à jour l'odomètre.
     */
    public function recordFuelConsumption(Vehicle $vehicle, array $data): VehicleConsumption
    {
        return DB::transaction(function () use ($vehicle, $data) {
            // On ne met à jour l'odomètre que s'il progresse (évite les erreurs de saisie rétroactives)
            if (isset($data['odometer_reading']) && $data['odometer_reading'] > $vehicle->current_odometer) {
                $vehicle->update(['current_odometer' => $data['odometer_reading']]);
            }

            return VehicleConsumption::updateOrCreate(
                ['external_transaction_id' => $data['external_transaction_id'] ?? null, 'vehicle_id' => $vehicle->id],
                [
                    'date' => $data['date'],
                    'quantity' => $data['quantity'],
                    'amount_ht' => $data['amount_ht'],
                    'odometer_reading' => $data['odometer_reading'] ?? $vehicle->current_odometer,
                    'source' => $data['source'] ?? 'manual',
                ]
            );
        });
    }
}
