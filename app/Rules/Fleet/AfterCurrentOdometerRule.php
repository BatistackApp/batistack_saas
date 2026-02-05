<?php

namespace App\Rules\Fleet;

use App\Models\Fleet\Vehicle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AfterCurrentOdometerRule implements ValidationRule
{
    /**
     * @param  int|string|null  $vehicleId  L'identifiant du véhicule concerné
     */
    public function __construct(protected int|string|null $vehicleId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($this->vehicleId)) {
            return;
        }

        $vehicle = Vehicle::find($this->vehicleId);

        if ($vehicle && $value <= $vehicle->current_odometer) {
            $fail("Le relevé odométrique (:value) doit être supérieur au relevé actuel du véhicule ({$vehicle->current_odometer} {$vehicle->odometer_unit}).");
        }
    }
}
