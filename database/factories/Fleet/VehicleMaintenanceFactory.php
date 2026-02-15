<?php

namespace Database\Factories\Fleet;

use App\Models\Core\Tenants;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleMaintenance;
use App\Models\Fleet\VehicleMaintenancePlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleMaintenanceFactory extends Factory
{
    protected $model = VehicleMaintenance::class;

    public function definition(): array
    {
        return [
            'technician_name' => $this->faker->name(),
            'maintenance_type' => $this->faker->word(),
            'maintenance_status' => $this->faker->word(),
            'description' => $this->faker->text(),
            'resolution_notes' => $this->faker->word(),
            'odometer_reading' => $this->faker->randomFloat(),
            'hours_reading' => $this->faker->randomFloat(),
            'cost_parts' => $this->faker->randomFloat(),
            'cost_labor' => $this->faker->randomFloat(),
            'total_cost' => $this->faker->randomFloat(),
            'reported_at' => Carbon::now(),
            'scheduled_at' => Carbon::now(),
            'started_at' => Carbon::now(),
            'completed_at' => Carbon::now(),
            'downtime_hours' => $this->faker->randomNumber(),
            'internal_reference' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'vehicle_id' => Vehicle::factory(),
            'vehicle_maintenance_plan_id' => VehicleMaintenancePlan::factory(),
            'reported_by' => User::factory(),
        ];
    }
}
