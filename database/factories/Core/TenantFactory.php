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
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'stripe_customer_id' => $this->faker->word(),
            'stripe_subscription_id' => $this->faker->word(),
            'subscription_expires_at' => Carbon::now(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'plan_id' => Plan::factory(),
        ];
    }
}
