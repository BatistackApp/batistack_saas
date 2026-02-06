<?php

namespace Database\Factories\Project;

use App\Models\HR\Employee;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectImputation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProjectImputationFactory extends Factory
{
    protected $model = ProjectImputation::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'amount' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'project_id' => Project::factory(),
            'employee_id' => Employee::factory(),
            'payroll_period_id' => PayrollPeriod::factory(),
        ];
    }
}
