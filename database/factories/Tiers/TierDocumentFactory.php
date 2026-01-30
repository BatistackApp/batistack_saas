<?php

namespace Database\Factories\Tiers;

use App\Models\Tiers\TierDocument;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TierDocumentFactory extends Factory
{
    protected $model = TierDocument::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'file_path' => $this->faker->word(),
            'expires_at' => $this->faker->word(),
            'status' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tiers_id' => Tiers::factory(),
        ];
    }
}
