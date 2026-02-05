<?php

namespace App\Jobs\Fleet;

use App\Models\Fleet\Vehicle;
use App\Services\Fleet\FleetConsumptionService;
use App\Services\Fleet\FleetTollService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAllVehiclesApiDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FleetConsumptionService $fuelService, FleetTollService $tollService): void
    {
        // On traite les véhicules actifs possédant des intégrations
        Vehicle::where('is_active', true)
            ->where(function ($query) {
                $query->whereNotNull('external_fuel_card_id')
                    ->orWhereNotNull('external_toll_tag_id');
            })
            ->chunk(50, function ($vehicles) use ($fuelService, $tollService) {
                foreach ($vehicles as $vehicle) {
                    if ($vehicle->external_fuel_card_id) {
                        $fuelService->syncFromExternalSource($vehicle);
                    }
                    if ($vehicle->external_toll_tag_id) {
                        $tollService->syncFromExternalSource($vehicle);
                    }
                }
            });
    }
}
