<?php

namespace Database\Factories\Fleet;

use App\Models\Core\Tenants;
use App\Models\Fleet\VehicleChecklistTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleChecklistTemplateFactory extends Factory
{
    protected $model = VehicleChecklistTemplate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'vehicle_type' => $this->faker->word(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
