<?php

namespace Database\Factories\Payroll;

use App\Models\Core\Tenant;
use App\Models\HR\Employee;
use App\Models\Payroll\MealAllowance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MealAllowanceFactory extends Factory
{
    protected $model = MealAllowance::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'type' => $this->faker->word(),
            'amount' => $this->faker->randomFloat(),
            'days_count' => $this->faker->randomNumber(),
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'employee_id' => Employee::factory(),
        ];
    }
}
