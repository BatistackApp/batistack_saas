<?php

namespace Database\Factories\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleConsumption;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleConsumptionFactory extends Factory
{
    protected $model = VehicleConsumption::class;

    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'date' => now(),
            'quantity' => $this->faker->randomFloat(2, 20, 80), // Litres ou kWh
            'amount_ht' => $this->faker->randomFloat(2, 30, 150),
            'odometer_reading' => $this->faker->numberBetween(1000, 150000),
            'is_full' => true,
            'source' => 'manual', // ou 'api_total', 'api_as24'
            'external_reference' => $this->faker->uuid(),
        ];
    }
}
