<?php

namespace Database\Factories\Tiers;

use App\Models\Tiers\TierAddress;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TierAddressFactory extends Factory
{
    protected $model = TierAddress::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'name' => $this->faker->name(),
            'street_address' => $this->faker->address(),
            'postal_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'additional_info' => $this->faker->word(),
            'is_default' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tiers_id' => Tiers::factory(),
        ];
    }
}
