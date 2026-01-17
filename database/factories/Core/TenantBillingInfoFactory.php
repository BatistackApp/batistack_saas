<?php

namespace Database\Factories\Core;

use App\Models\Core\Tenant;
use App\Models\Core\TenantBillingInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantBillingInfoFactory extends Factory
{
    protected $model = TenantBillingInfo::class;

    public function definition(): array
    {
        return [
            'company_name' => $this->faker->unique()->company(),
            'billing_email' => $this->faker->unique()->safeEmail(),
            'billing_address' => $this->faker->streetAddress(),
            'postal_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'vat_number' => $this->generateVatNumber(),
            'phone' => $this->faker->phoneNumber(),
            'tenant_id' => Tenant::factory(),
        ];
    }

    public function france(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'France',
            'vat_number' => 'FR'.$this->faker->numerify('##############'),
            'postal_code' => $this->faker->numerify('#####'),
        ]);
    }

    public function belgium(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Belgium',
            'vat_number' => 'BE'.$this->faker->numerify('##########'),
            'postal_code' => $this->faker->numerify('####'),
        ]);
    }

    public function switzerland(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Switzerland',
            'vat_number' => 'CHE'.$this->faker->numerify('###########'),
            'postal_code' => $this->faker->numerify('####'),
        ]);
    }

    public function withoutTenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => null,
        ]);
    }

    private function generateVatNumber(): string
    {
        $countries = ['FR', 'BE', 'CH', 'DE', 'IT', 'ES'];
        $prefix = $this->faker->randomElement($countries);

        return $prefix.$this->faker->numerify('############');
    }
}
