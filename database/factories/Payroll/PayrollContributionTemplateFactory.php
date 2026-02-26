<?php

namespace Database\Factories\Payroll;

use App\Models\Core\Tenants;
use App\Models\Payroll\PayrollContributionTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollContributionTemplateFactory extends Factory
{
    protected $model = PayrollContributionTemplate::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'label' => 'Cotisation '.$this->faker->word,
            'employee_rate' => $this->faker->randomFloat(4, 1, 15),
            'employer_rate' => $this->faker->randomFloat(4, 10, 30),
            'applicable_to' => 'ouvrier',
            'is_active' => true,
        ];
    }

    public function urssaf(): static
    {
        return $this->state(fn () => [
            'label' => 'URSSAF Vieillesse plafonnée',
            'employee_rate' => 6.90,
            'employer_rate' => 8.55,
        ]);
    }

    public function proBtp(): static
    {
        return $this->state(fn () => [
            'label' => 'Retraite PRO BTP T1',
            'employee_rate' => 4.05,
            'employer_rate' => 6.00,
        ]);
    }
}
