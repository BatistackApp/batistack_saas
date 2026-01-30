<?php

namespace Database\Factories\Projects;

use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'code_project' => $this->faker->word(),
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'address' => $this->faker->address(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'initial_budget_ht' => $this->faker->randomFloat(),
            'status' => $this->faker->word(),
            'planned_start_at' => Carbon::now(),
            'planned_end_at' => Carbon::now(),
            'actual_start_at' => Carbon::now(),
            'actual_end_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'customer_id' => Tiers::factory(),
        ];
    }
}
