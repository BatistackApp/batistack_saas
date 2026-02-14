<?php

namespace Database\Factories\Pilotage;

use App\Enums\Pilotage\KpiCategory;
use App\Enums\Pilotage\KpiUnit;
use App\Models\Core\Tenants;
use App\Models\Pilotage\KpiIndicator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class KpiIndicatorFactory extends Factory
{
    protected $model = KpiIndicator::class;

    public function definition(): array
    {
        return [
            'ulid' => Str::ulid(),
            'code' => $this->faker->unique()->slug(2, '_'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->text(),
            'category' => $this->faker->randomElement(KpiCategory::cases()),
            'unit' => $this->faker->randomElement(KpiUnit::cases()),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
