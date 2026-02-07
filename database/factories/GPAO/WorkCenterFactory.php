<?php

namespace Database\Factories\GPAO;

use App\Models\Core\Tenants;
use App\Models\GPAO\WorkCenter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class WorkCenterFactory extends Factory
{
    protected $model = WorkCenter::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'capacity_per_day' => $this->faker->randomFloat(),
            'hourly_rate' => $this->faker->randomFloat(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
