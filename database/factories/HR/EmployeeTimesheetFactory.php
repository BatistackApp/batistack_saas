<?php

namespace Database\Factories\HR;

use App\Models\HR\Employee;
use App\Models\HR\EmployeeTimesheet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EmployeeTimesheetFactory extends Factory
{
    protected $model = EmployeeTimesheet::class;

    public function definition(): array
    {
        return [
            'timesheet_date' => Carbon::now(),
            'total_hours_work' => $this->faker->randomFloat(),
            'total_hours_travel' => $this->faker->randomFloat(),
            'status' => $this->faker->word(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'employee_id' => Employee::factory(),
        ];
    }
}
