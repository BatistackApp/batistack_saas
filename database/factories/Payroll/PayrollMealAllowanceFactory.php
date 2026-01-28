<?php

namespace Database\Factories\Payroll;

use App\Models\Payroll\PayrollMealAllowance;
use App\Models\Payroll\PayrollSlip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollMealAllowanceFactory extends Factory
{
    protected $model = PayrollMealAllowance::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'amount' => $this->faker->randomFloat(),
            'days_count' => $this->faker->randomNumber(),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'payroll_slip_id' => PayrollSlip::factory(),
        ];
    }
}
