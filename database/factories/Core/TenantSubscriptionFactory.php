<?php

namespace Database\Factories\Core;

use App\Enums\Core\BillingPeriod;
use App\Enums\Core\SubscriptionStatus;
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
            'billing_period' => BillingPeriod::Monthly,
            'status' => SubscriptionStatus::Active,
            'stripe_subscription_id' => 'sub_' . $this->faker->unique()->md5(),
            'trial_ends_at' => Carbon::now()->addDays(14),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth(),
            'cancelled_at' => null,
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Active,
            'cancelled_at' => null,
        ]);
    }

    public function onTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Active,
            'trial_ends_at' => Carbon::now()->addDays(7),
            'cancelled_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => Carbon::now()->subDays(3),
        ]);
    }

    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::PastDue,
            'cancelled_at' => null,
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_period' => BillingPeriod::Yearly,
            'current_period_end' => Carbon::now()->addYear(),
        ]);
    }
}
