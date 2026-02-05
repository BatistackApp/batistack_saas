<?php

namespace Database\Factories\Fleet;

use App\Models\Core\Tenants;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleAssignment;
use App\Models\Projects\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleAssignmentFactory extends Factory
{
    protected $model = VehicleAssignment::class;

    public function definition(): array
    {
        return [
            'started_at' => Carbon::now(),
            'ended_at' => Carbon::now(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'vehicle_id' => Vehicle::factory(),
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
        ];
    }
}
