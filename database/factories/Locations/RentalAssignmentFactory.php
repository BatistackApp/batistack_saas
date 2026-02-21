<?php

namespace Database\Factories\Locations;

use App\Models\Core\Tenants;
use App\Models\Locations\RentalAssignment;
use App\Models\Locations\RentalContract;
use App\Models\Projects\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RentalAssignmentFactory extends Factory
{
    protected $model = RentalAssignment::class;

    public function definition(): array
    {
        return [
            'assigned_at' => Carbon::now(),
            'released_at' => Carbon::now(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'rental_contract_id' => RentalContract::factory(),
            'project_id' => Project::factory(),
        ];
    }
}
