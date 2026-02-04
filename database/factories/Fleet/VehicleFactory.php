<?php

namespace Database\Factories\Fleet;

use App\Models\Core\Tenants;
use App\Models\Fleet\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'internal_code' => $this->faker->word(),
            'type' => $this->faker->word(),
            'license_plate' => $this->faker->word(),
            'brand' => $this->faker->word(),
            'model' => $this->faker->word(),
            'vin' => $this->faker->word(),
            'fuel_type' => $this->faker->word(),
            'external_fuel_card_id' => $this->faker->word(),
            'external_toll_tag_id' => $this->faker->word(),
            'hourly_rate' => $this->faker->randomFloat(),
            'km_rate' => $this->faker->randomFloat(),
            'current_odometer' => $this->faker->randomFloat(),
            'odometer_unit' => $this->faker->word(),
            'purchase_date' => Carbon::now(),
            'purchase_price' => $this->faker->randomFloat(),
            'last_external_sync_at' => Carbon::now(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
