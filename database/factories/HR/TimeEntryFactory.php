<?php

namespace Database\Factories\HR;

use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\HR\TimeEntry;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        return [
            'date' => Carbon::now(),
            'hours' => $this->faker->randomFloat(),
            'status' => $this->faker->word(),
            'has_meal_allowance' => $this->faker->boolean(),
            'has_host_allowance' => $this->faker->boolean(),
            'travel_time' => $this->faker->randomFloat(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'employee_id' => Employee::factory(),
            'project_id' => Project::factory(),
            'project_phase_id' => ProjectPhase::factory(),
            'verified_by' => User::factory(),
        ];
    }
}
