<?php

namespace Database\Factories\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleInspection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleInspectionFactory extends Factory
{
    protected $model = VehicleInspection::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'inspection_date' => Carbon::now(),
            'next_due_date' => Carbon::now(),
            'result' => $this->faker->word(),
            'report_path' => $this->faker->word(),
            'observation' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'vehicle_id' => Vehicle::factory(),
        ];
    }
}
