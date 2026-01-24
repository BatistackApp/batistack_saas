<?php

namespace Database\Factories\HR;

use App\Models\Core\Tenant;
use App\Models\HR\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'employee_number' => $this->faker->word(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'hire_date' => $this->faker->dateTimeBetween('-1 year'),
            'resignation_date' => $this->faker->boolean ? null : Carbon::now(),
            'status' => $this->faker->boolean(),
            'notes' => $this->faker->boolean ?? $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
            'tenant_id' => Tenant::factory(),
        ];
    }
}
