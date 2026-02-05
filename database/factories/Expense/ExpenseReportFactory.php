<?php

namespace Database\Factories\Expense;

use App\Enums\Expense\ExpenseStatus;
use App\Models\Core\Tenants;
use App\Models\Expense\ExpenseReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ExpenseReportFactory extends Factory
{
    protected $model = ExpenseReport::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'user_id' => User::factory(),
            'label' => 'Frais de ' . $this->faker->monthName() . ' ' . date('Y'),
            'status' => ExpenseStatus::Draft,
            'amount_ht' => 0,
            'amount_tva' => 0,
            'amount_ttc' => 0,
        ];
    }
}
