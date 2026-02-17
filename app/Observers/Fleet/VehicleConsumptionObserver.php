<?php

namespace App\Observers\Fleet;

use App\Models\Fleet\VehicleConsumption;

class VehicleConsumptionObserver
{
    public function created(VehicleConsumption $consumption): void
    {
        $vehicle = $consumption->vehicle;

        // Mise à jour de l'odomètre du véhicule si la saisie est plus récente
        if ($consumption->odometer_reading > $vehicle->current_odometer) {
            $vehicle->update([
                'current_odometer' => $consumption->odometer_reading,
            ]);

            // Le VehicleObserver (déjà existant) prendra le relais pour lancer
            // le CheckVehicleComplianceJob suite au changement d'odomètre.
        }
    }
}
