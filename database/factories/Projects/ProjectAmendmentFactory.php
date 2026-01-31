<?php

namespace Database\Factories\Projects;

use App\Models\Projects\Project;
use App\Models\Projects\ProjectAmendment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProjectAmendmentFactory extends Factory
{
    protected $model = ProjectAmendment::class;

    public function definition(): array
    {
        return [
            'reference' => $this->faker->word(),
            'description' => $this->faker->text(),
            'amount_ht' => $this->faker->randomFloat(),
            'status' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'project_id' => Project::factory(),
        ];
    }
}
