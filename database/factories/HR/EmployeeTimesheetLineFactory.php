<?php

namespace Database\Factories\HR;

use App\Models\Chantiers\Chantier;
use App\Models\HR\EmployeeTimesheet;
use App\Models\HR\EmployeeTimesheetLine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EmployeeTimesheetLineFactory extends Factory
{
    protected $model = EmployeeTimesheetLine::class;

    public function definition(): array
    {
        return [
            'hours_work' => $this->faker->randomFloat(),
            'hours_travel' => $this->faker->randomFloat(),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'employee_timesheet_id' => EmployeeTimesheet::factory(),
            'chantier_id' => Chantier::factory(),
        ];
    }
}
