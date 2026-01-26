<?php

namespace Database\Factories\Payroll;

use App\Models\Payroll\PayrollOvertime;
use App\Models\Payroll\PayrollSlip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollOvertimeFactory extends Factory
{
    protected $model = PayrollOvertime::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'type' => $this->faker->word(),
            'hours' => $this->faker->randomFloat(),
            'hourly_rate' => $this->faker->randomFloat(),
            'multiplier' => $this->faker->randomNumber(),
            'amount' => $this->faker->randomFloat(),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'payroll_slip_id' => PayrollSlip::factory(),
        ];
    }
}
