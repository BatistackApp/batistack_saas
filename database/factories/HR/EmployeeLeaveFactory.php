<?php

namespace Database\Factories\HR;

use App\Models\HR\Employee;
use App\Models\HR\EmployeeLeave;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EmployeeLeaveFactory extends Factory
{
    protected $model = EmployeeLeave::class;

    public function definition(): array
    {
        return [
            'leave_type' => $this->faker->word(),
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now(),
            'status' => $this->faker->word(),
            'reason' => $this->faker->word(),
            'rejection_reason' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'employee_id' => Employee::factory(),
        ];
    }
}
