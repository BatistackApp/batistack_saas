<?php

namespace Database\Factories\Chantiers;

use App\Enums\Chantiers\CostCategory;
use App\Models\Chantiers\Chantier;
use App\Models\Chantiers\ChantierCost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ChantierCostFactory extends Factory
{
    protected $model = ChantierCost::class;

    public function definition(): array
    {
        return [
            'category' => $this->faker->randomElement(CostCategory::cases()),
            'label' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->sentence(),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'cost_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'reference' => $this->faker->optional()->bothify('REF-####'),

            'chantier_id' => Chantier::factory(),
        ];
    }
}
