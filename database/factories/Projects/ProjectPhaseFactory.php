<?php

namespace Database\Factories\Projects;

use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProjectPhaseFactory extends Factory
{
    protected $model = ProjectPhase::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'allocated_budget' => $this->faker->randomFloat(),
            'order' => $this->faker->randomNumber(),
            'status' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'project_id' => Project::factory(),
        ];
    }
}
