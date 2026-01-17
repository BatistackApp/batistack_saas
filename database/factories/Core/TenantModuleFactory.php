<?php

namespace Database\Factories\Core;

use App\Models\Core\Module;
use App\Models\Core\Tenant;
use App\Models\Core\TenantModule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TenantModuleFactory extends Factory
{
    protected $model = TenantModule::class;

    public function definition(): array
    {
        return [
            'billing_period' => $this->faker->randomElement(['monthly', 'yearly', 'quarterly', 'one-time']),
            'is_active' => true,
            'stripe_subscription_id' => 'sub_' . $this->faker->unique()->md5(),
            'subscribed_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 90)),
            'expires_at' => Carbon::now()->addMonths($this->faker->numberBetween(1, 12)),
            'tenant_id' => Tenant::factory(),
            'module_id' => Module::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'expires_at' => Carbon::now()->addMonths($this->faker->numberBetween(1, 12)),
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
            'expires_at' => Carbon::now()->subDays(10),
        ]);
    }

    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays(3),
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_period' => 'monthly',
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_period' => 'yearly',
            'expires_at' => Carbon::now()->addYears(1),
        ]);
    }
}
