<?php

namespace Database\Factories\Tiers;

use App\Enums\Tiers\TierDocumentStatus;
use App\Enums\Tiers\TierDocumentType;
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
            'type' => $this->faker->randomElement(TierDocumentType::cases()),
            'file_path' => $this->faker->word(),
            'expires_at' => $this->faker->date(),
            'status' => $this->faker->randomElement(TierDocumentStatus::cases()),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tiers_id' => Tiers::factory(),
        ];
    }
}
