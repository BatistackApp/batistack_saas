<?php

namespace Database\Factories\Fleet;

use App\Enums\Fleet\FuelType;
use App\Enums\Fleet\VehicleType;
use App\Models\Core\Tenants;
use App\Models\Fleet\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'name' => $this->faker->randomElement(['Renault Master', 'Caterpillar 320', 'Peugeot Partner', 'Iveco Daily']),
            'internal_code' => 'MAT-'.$this->faker->unique()->bothify('####'),
            'license_plate' => $this->faker->bothify('??-###-??'),
            'type' => $this->faker->randomElement(VehicleType::cases()),
            'fuel_type' => $this->faker->randomElement(FuelType::class),
            'current_odometer' => $this->faker->numberBetween(1000, 150000),
            'odometer_unit' => 'km',
            'hourly_rate' => $this->faker->randomFloat(2, 15, 85), // Coût de possession horaire
            'km_rate' => $this->faker->randomFloat(2, 0.20, 1.50),  // Coût au kilomètre
            'is_active' => true,
        ];
    }
}
