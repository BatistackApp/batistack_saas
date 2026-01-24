<?php

namespace Database\Factories\HR;

use App\Models\HR\Employee;
use App\Models\HR\EmployeeRate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EmployeeRateFactory extends Factory
{
    protected $model = EmployeeRate::class;

    public function definition(): array
    {
        return [
            'hourly_rate' => $this->faker->randomFloat(),
            'effective_from' => Carbon::now(),
            'effective_to' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'employee_id' => Employee::factory(),
        ];
    }
}
