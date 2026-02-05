<?php

namespace Database\Factories\Expense;

use App\Models\Core\Tenants;
use App\Models\Expense\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ExpenseCategoryFactory extends Factory
{
    protected $model = ExpenseCategory::class;

    public function definition(): array
    {
        return [
            'tenants_id' => 1, // À surcharger dans les tests
            'name' => $this->faker->randomElement(['Restauration', 'Hébergement', 'Kilomètres', 'Petit Outillage']),
            'icon' => 'heroicon-o-tag',
            'requires_distance' => $this->faker->boolean(25),
            'is_active' => true,
        ];
    }
}
