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
            'billing_period' => $this->faker->word(),
            'is_active' => $this->faker->boolean(),
            'stripe_subscription_id' => $this->faker->word(),
            'subscribed_at' => Carbon::now(),
            'expires_at' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'module_id' => Module::factory(),
        ];
    }
}
