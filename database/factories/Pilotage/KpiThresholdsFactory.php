<?php

namespace Database\Factories\Pilotage;

use App\Enums\Pilotage\ThresholdSeverity;
use App\Models\Core\Tenants;
use App\Models\Pilotage\KpiIndicator;
use App\Models\Pilotage\KpiThresholds;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class KpiThresholdsFactory extends Factory
{
    protected $model = KpiThresholds::class;

    public function definition(): array
    {
        return [
            'ulid' => Str::ulid(),
            'min_value' => $this->faker->randomFloat(),
            'max_value' => $this->faker->randomFloat(),
            'severity' => $this->faker->randomElement(ThresholdSeverity::cases()),
            'is_notifiable' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'kpi_indicator_id' => KpiIndicator::factory(),
        ];
    }
}
