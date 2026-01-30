<?php

namespace Database\Factories\Tiers;

use App\Models\Tiers\TierDocumentRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TierDocumentRequirementFactory extends Factory
{
    protected $model = TierDocumentRequirement::class;

    public function definition(): array
    {
        return [
            'tier_type' => $this->faker->word(),
            'document_type' => $this->faker->word(),
            'is_mandatory' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
