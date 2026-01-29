<?php

namespace Database\Factories\Tiers;

use App\Models\Tiers\Tiers;
use App\Models\Tiers\TierType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TierTypeFactory extends Factory
{
    protected $model = TierType::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'is_primary' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tiers_id' => Tiers::factory(),
        ];
    }
}
