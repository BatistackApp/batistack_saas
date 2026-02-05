<?php

namespace Database\Factories\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleConsumption;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleConsumptionFactory extends Factory
{
    protected $model = VehicleConsumption::class;

    public function definition(): array
    {
        return [
            'date' => Carbon::now(),
            'quantity' => $this->faker->randomFloat(),
            'amount_ht' => $this->faker->randomFloat(),
            'odometer_reading' => $this->faker->randomFloat(),
            'source' => $this->faker->word(),
            'external_transaction_id' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'vehicle_id' => Vehicle::factory(),
        ];
    }
}
