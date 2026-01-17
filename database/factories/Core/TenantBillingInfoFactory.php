<?php

namespace Database\Factories\Core;

use App\Models\Core\Tenant;
use App\Models\Core\TenantBillingInfo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TenantBillingInfoFactory extends Factory
{
    protected $model = TenantBillingInfo::class;

    public function definition(): array
    {
        return [
            'company_name' => $this->faker->name(),
            'billing_email' => $this->faker->unique()->safeEmail(),
            'billing_address' => $this->faker->address(),
            'postal_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'vat_number' => $this->faker->word(),
            'phone' => $this->faker->phoneNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
        ];
    }
}
