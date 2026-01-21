<?php

namespace Database\Factories\Chantiers;

use App\Enums\Chantiers\CostCategory;
use App\Models\Chantiers\Chantier;
use App\Models\Chantiers\ChantierBudget;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ChantierBudgetFactory extends Factory
{
    protected $model = ChantierBudget::class;

    public function definition(): array
    {
        return [
            'category' => $this->faker->randomElement(CostCategory::cases()),
            'planned_amount' => $this->faker->randomFloat(2, 1000, 50000),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'chantier_id' => Chantier::factory(),
        ];
    }
}
