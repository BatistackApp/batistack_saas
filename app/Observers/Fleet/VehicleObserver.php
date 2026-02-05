<?php

namespace App\Observers\Fleet;

use App\Jobs\Fleet\CheckVehicleComplianceJob;
use App\Models\Fleet\Vehicle;

class VehicleObserver
{
    public function updated(Vehicle $vehicle): void
    {
        // Si le compteur a été mis à jour (via saisie manuelle ou API)
        if ($vehicle->wasChanged('current_odometer')) {
            // On lance un audit de conformité pour voir si un seuil de maintenance est franchi
            CheckVehicleComplianceJob::dispatch($vehicle);
        }
    }
}
