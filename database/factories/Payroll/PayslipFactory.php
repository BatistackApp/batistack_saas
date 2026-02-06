<?php

namespace Database\Factories\Payroll;

use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\Payslip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayslipFactory extends Factory
{
    protected $model = Payslip::class;

    public function definition(): array
    {
        $grossAmount = $this->faker->randomFloat(2, 1500, 3500);
        $socialDeduction = $grossAmount * 0.08; // ~8% de cotisations
        $netSocialAmount = $grossAmount - $socialDeduction;
        $personalTax = $netSocialAmount * 0.05; // ~5% d'impôt approximatif
        $netToPay = $netSocialAmount - $personalTax;

        return [
            'gross_amount' => $grossAmount,
            'net_social_amount' => $netSocialAmount,
            'net_to_pay' => $netToPay,
            'pas_rate' => 0.065, // Taux moyen BTP
            'pas_amount' => $grossAmount * 0.065,
            'status' => $this->faker->randomElement(['draft', 'validated', 'paid']),
            'metadata' => [
                'hours_worked' => $this->faker->numberBetween(150, 180),
                'overtime_hours' => $this->faker->numberBetween(0, 10),
                'meal_allowance' => $this->faker->boolean(70) ? 5.5 : 0,
            ],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'payroll_period_id' => PayrollPeriod::factory(),
            'employee_id' => Employee::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function validated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'validated',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }
}
