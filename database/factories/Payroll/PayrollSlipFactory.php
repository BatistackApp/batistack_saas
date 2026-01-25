<?php

namespace Database\Factories\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Models\Core\Tenant;
use App\Models\HR\Employee;
use App\Models\Payroll\PayrollSlip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollSlipFactory extends Factory
{
    protected $model = PayrollSlip::class;

    public function definition(): array
    {
        $year = $this->faker->year();
        $month = \Illuminate\Support\now()->monthOfYear();
        $periodStart = \Carbon\Carbon::createFromDate($year, $month, 1);
        $periodEnd = $periodStart->clone()->endOfMonth();

        return [
            'uuid' => $this->faker->uuid(),
            'year' => $year,
            'month' => $month,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => PayrollStatus::Draft,
            'total_hours_work' => $this->faker->numberBetween(160, 180),
            'total_hours_travel' => $this->faker->numberBetween(0, 10),
            'gross_amount' => $this->faker->numberBetween(2000, 3500),
            'social_contributions' => $this->faker->numberBetween(500, 1000),
            'employee_deduction' => $this->faker->numberBetween(50, 200),
            'net_amount' => $this->faker->numberBetween(1500, 2500),
            'transport_amount' => $this->faker->numberBetween(0, 100),
            'notes' => $this->faker->word(),

            'tenant_id' => Tenant::factory(),
            'employee_id' => Employee::factory(),
        ];
    }

    public function validated(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => PayrollStatus::Validated,
                'validated_at' => now(),
            ];
        });
    }

    public function exported(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => PayrollStatus::Exported,
                'validated_at' => now(),
                'exported_at' => now(),
            ];
        });
    }
}
