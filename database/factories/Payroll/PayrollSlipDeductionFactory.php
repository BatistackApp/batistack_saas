<?php

namespace Database\Factories\Payroll;

use App\Enums\Payroll\PayrollDeductionType;
use App\Models\Payroll\PayrollSlip;
use App\Models\Payroll\PayrollSlipDeduction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollSlipDeductionFactory extends Factory
{
    protected $model = PayrollSlipDeduction::class;

    public function definition(): array
    {
        return [
            'payroll_slip_id' => PayrollSlip::factory(),
            'type' => $this->faker->randomElement(PayrollDeductionType::cases()),
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->numberBetween(10, 200),
            'reason' => $this->faker->optional()->sentence(),
        ];
    }
}
