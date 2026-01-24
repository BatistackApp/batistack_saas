<?php

namespace Database\Factories\Payroll;

use App\Models\Chantiers\Chantier;
use App\Models\Payroll\PayrollSlip;
use App\Models\Payroll\PayrollSlipLine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollSlipLineFactory extends Factory
{
    protected $model = PayrollSlipLine::class;

    public function definition(): array
    {
        $hoursWork = $this->faker->numberBetween(1, 10);
        $hourlyRate = $this->faker->numberBetween(15, 30);

        return [
            'description' => $this->faker->sentence(),
            'hours_work' => $hoursWork,
            'hours_travel' => $this->faker->numberBetween(0,2),
            'hourly_rate' => $hourlyRate,
            'amount' => $hoursWork * $hourlyRate,

            'payroll_slip_id' => PayrollSlip::factory(),
            'chantier_id' => Chantier::factory(),
        ];
    }
}
