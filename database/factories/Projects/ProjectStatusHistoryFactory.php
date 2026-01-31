<?php

namespace Database\Factories\Projects;

use App\Models\Projects\Project;
use App\Models\Projects\ProjectStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProjectStatusHistoryFactory extends Factory
{
    protected $model = ProjectStatusHistory::class;

    public function definition(): array
    {
        return [
            'old_status' => $this->faker->word(),
            'new_status' => $this->faker->word(),
            'reason' => $this->faker->word(),
            'changed_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'project_id' => Project::factory(),
            'changed_by_user_id' => User::factory(),
        ];
    }
}
