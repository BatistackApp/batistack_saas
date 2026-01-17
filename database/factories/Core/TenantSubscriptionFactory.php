<?php

namespace Database\Factories\Core;

use App\Models\Core\Plan;
use App\Models\Core\Tenant;
use App\Models\Core\TenantSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TenantSubscriptionFactory extends Factory
{
    protected $model = TenantSubscription::class;

    public function definition(): array
    {
        return [
            'billing_period' => $this->faker->word(),
            'status' => $this->faker->word(),
            'stripe_subscription_id' => $this->faker->word(),
            'trial_ends_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now(),
            'cancelled_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
        ];
    }
}
