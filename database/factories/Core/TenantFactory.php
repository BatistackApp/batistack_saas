<?php

namespace Database\Factories\Core;

use App\Models\Core\Plan;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'slug' => $this->faker->unique()->slug(),
            'stripe_customer_id' => 'cus_' . $this->faker->unique()->md5(),
            'stripe_subscription_id' => 'sub_' . $this->faker->unique()->md5(),
            'subscription_expires_at' => Carbon::now()->addMonth(),
            'is_active' => true,
            'plan_id' => Plan::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'subscription_expires_at' => Carbon::now()->addMonth(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'subscription_expires_at' => Carbon::now()->subDays(10),
        ]);
    }

    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'subscription_expires_at' => Carbon::now()->addDays(3),
        ]);
    }

    public function withoutPlan(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_id' => null,
        ]);
    }
}
