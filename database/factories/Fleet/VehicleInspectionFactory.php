<?php

namespace Database\Factories\Fleet;

use App\Enums\Fleet\InspectionType;
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
            'vehicle_id' => Vehicle::factory(),
            'type' => $this->faker->randomElement(InspectionType::cases()),
            'inspection_date' => now()->subMonths(1),
            'next_due_date' => now()->addMonths(5),
            'report_path' => null,
            'observations' => $this->faker->text(100),
        ];
    }
}
