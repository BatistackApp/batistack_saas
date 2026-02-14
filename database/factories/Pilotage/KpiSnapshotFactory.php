<?php

namespace Database\Factories\Pilotage;

use App\Models\Core\Tenants;
use App\Models\Pilotage\KpiIndicator;
use App\Models\Pilotage\KpiSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class KpiSnapshotFactory extends Factory
{
    protected $model = KpiSnapshot::class;

    public function definition(): array
    {
        return [
            'ulid' => Str::ulid(),
            'value' => $this->faker->randomFloat(),
            'measured_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'kpi_indicator_id' => KpiIndicator::factory(),
            'tenants_id' => Tenants::factory(),
        ];
    }
}
