<?php

namespace Database\Factories\Core;

use App\Models\Core\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'monthly_price' => $this->faker->randomFloat(),
            'yearly_price' => $this->faker->randomFloat(),
            'stripe_monthly_price_id' => $this->faker->word(),
            'stripe_yearly_price_id' => $this->faker->word(),
            'is_active' => true,
            'max_users' => $this->faker->randomDigit(),
            'max_projects' => $this->faker->randomDigit(),
        ];
    }
}
