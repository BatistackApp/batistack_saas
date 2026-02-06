<?php

namespace Database\Factories\Payroll;

use App\Enums\Payroll\PayslipLineType;
use App\Models\Payroll\Payslip;
use App\Models\Payroll\PayslipLine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayslipLineFactory extends Factory
{
    protected $model = PayslipLine::class;

    public function definition(): array
    {
        $base = $this->faker->randomFloat(2, 100, 2500);
        $rate = $this->faker->randomFloat(4, 0, 1);
        $amountGain = $base * $rate;

        return [
            'label' => $this->faker->randomElement([
                'Salaire de base',
                'Heures supplémentaires 25%',
                'Heures supplémentaires 50%',
                'Indemnité repas',
                'Indemnité trajet',
                'Prime d\'ancienneté',
                'Bonus de productivité',
            ]),
            'base' => $base,
            'rate' => $rate,
            'amount_gain' => $amountGain,
            'amount_deduction' => $this->faker->randomFloat(2, 0, 500),
            'employer_amount' => $base * $this->faker->randomFloat(4, 0.08, 0.42),
            'type' => $this->faker->randomElement(PayslipLineType::cases()),
            'sort_order' => $this->faker->numberBetween(1, 50),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'payslip_id' => Payslip::factory(),
        ];
    }

    public function salary(): static
    {
        return $this->state(fn (array $attributes) => [
            'label' => 'Salaire de base',
            'type' => 'gain',
            'sort_order' => 10,
        ]);
    }

    public function allowance(): static
    {
        return $this->state(fn (array $attributes) => [
            'label' => 'Indemnité repas',
            'type' => 'gain',
            'sort_order' => 20,
        ]);
    }

    public function socialDeduction(): static
    {
        return $this->state(fn (array $attributes) => [
            'label' => 'Cotisations sociales',
            'type' => 'deduction',
            'sort_order' => 40,
        ]);
    }
}
