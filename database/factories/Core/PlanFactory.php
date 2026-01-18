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
            'name' => fake()->word(),
            'slug' => fake()->slug(),
            'monthly_price' => $this->faker->randomFloat(2, 29, 299),
            'yearly_price' => $this->faker->randomFloat(2, 290, 2990),
            'stripe_monthly_price_id' => 'price_' . fake()->md5(),
            'stripe_yearly_price_id' => 'price_' . fake()->md5(),
            'is_active' => true,
            'max_users' => $this->faker->numberBetween(1, 50),
            'max_projects' => $this->faker->numberBetween(5, 100),
        ];
    }

    public function starter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Starter',
            'slug' => 'starter',
            'monthly_price' => 29.00,
            'yearly_price' => 290.00,
            'max_users' => 3,
            'max_projects' => 5,
        ]);
    }

    public function professional(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Professional',
            'slug' => 'professional',
            'monthly_price' => 99.00,
            'yearly_price' => 990.00,
            'max_users' => 10,
            'max_projects' => 25,
        ]);
    }

    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'monthly_price' => 299.00,
            'yearly_price' => 2990.00,
            'max_users' => 50,
            'max_projects' => 100,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
