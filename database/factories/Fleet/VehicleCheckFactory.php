<?php

namespace Database\Factories\Fleet;

use App\Models\Core\Tenants;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleAssignment;
use App\Models\Fleet\VehicleCheck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleCheckFactory extends Factory
{
    protected $model = VehicleCheck::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'has_anomalie' => $this->faker->boolean(),
            'odometer_reading' => $this->faker->randomFloat(),
            'general_note' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'vehicle_id' => Vehicle::factory(),
            'user_id' => User::factory(),
            'vehicle_assignment_id' => VehicleAssignment::factory(),
        ];
    }
}
