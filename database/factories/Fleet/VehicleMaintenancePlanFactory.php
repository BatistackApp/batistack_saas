<?php

namespace Database\Factories\Fleet;

use App\Models\Core\Tenants;
use App\Models\Fleet\VehicleMaintenancePlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleMaintenancePlanFactory extends Factory
{
    protected $model = VehicleMaintenancePlan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'vehicle_type' => $this->faker->word(),
            'interval_km' => $this->faker->randomNumber(),
            'interval_hours' => $this->faker->randomNumber(),
            'interval_month' => $this->faker->randomNumber(),
            'operations' => $this->faker->words(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
