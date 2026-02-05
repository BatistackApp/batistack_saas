<?php

namespace Database\Factories\Expense;

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
            'label' => $this->faker->word(),
            'amount_ht' => $this->faker->randomFloat(),
            'amount_tva' => $this->faker->randomFloat(),
            'amount_ttc' => $this->faker->randomFloat(),
            'status' => $this->faker->word(),
            'submitted_at' => Carbon::now(),
            'validated_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'user_id' => User::factory(),
            'validated_by' => User::factory(),
        ];
    }
}
