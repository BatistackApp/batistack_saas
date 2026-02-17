<?php

namespace Database\Factories\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Models\Core\Tenants;
use App\Models\Payroll\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PayrollPeriodFactory extends Factory
{
    protected $model = PayrollPeriod::class;

    public function definition(): array
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return [
            'name' => 'Période '.$startDate->format('m/Y'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $this->faker->randomElement(PayrollStatus::cases()),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'tenants_id' => Tenants::factory(),
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }

    public function validated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'validated',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }
}
