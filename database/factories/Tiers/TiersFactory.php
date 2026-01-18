<?php

namespace Database\Factories\Tiers;

use App\Models\Core\Tenant;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TiersFactory extends Factory
{
    protected $model = Tiers::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->text(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'website' => $this->faker->word(),
            'siret' => $this->faker->word(),
            'vat_number' => $this->faker->word(),
            'iban' => $this->faker->word(),
            'bic' => $this->faker->word(),
            'types' => $this->faker->word(),
            'discount_percentage' => $this->faker->randomFloat(),
            'payment_delay_days' => $this->faker->randomNumber(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
        ];
    }
}
