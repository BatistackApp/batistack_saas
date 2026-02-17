<?php

namespace Database\Factories\Expense;

use App\Enums\Expense\ExpenseStatus;
use App\Models\Core\Tenants;
use App\Models\Expense\ExpenseReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseReportFactory extends Factory
{
    protected $model = ExpenseReport::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'user_id' => User::factory(),
            'label' => $this->faker->sentence(3),
            'status' => ExpenseStatus::Draft,
            'amount_ht' => 0,
            'amount_tva' => 0,
            'amount_ttc' => 0,
        ];
    }

    public function submitted(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExpenseStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }

    public function rejected(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExpenseStatus::Rejected,
            'rejection_reason' => 'Justificatif illisible.',
        ]);
    }
}
