<?php

namespace Database\Factories\Payroll;

use App\Models\Payroll\PayrollSlip;
use App\Models\Payroll\PayrollTravelAllowances;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollTravelAllowancesFactory extends Factory
{
    protected $model = PayrollTravelAllowances::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'amount' => $this->faker->randomFloat(),
            'distance_km' => $this->faker->randomFloat(),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'payroll_slip_id' => PayrollSlip::factory(),
        ];
    }
}
