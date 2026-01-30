<?php

namespace Database\Factories\Tiers;

use App\Models\Tiers\TierQualification;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TierQualificationFactory extends Factory
{
    protected $model = TierQualification::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->word(),
            'reference' => $this->faker->word(),
            'valid_until' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tiers_id' => Tiers::factory(),
        ];
    }
}
