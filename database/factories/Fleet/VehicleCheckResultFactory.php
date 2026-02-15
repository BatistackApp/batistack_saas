<?php

namespace Database\Factories\Fleet;

use App\Models\Fleet\VehicleCheck;
use App\Models\Fleet\VehicleChecklistQuestion;
use App\Models\Fleet\VehicleCheckResult;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleCheckResultFactory extends Factory
{
    protected $model = VehicleCheckResult::class;

    public function definition(): array
    {
        return [
            'value' => $this->faker->word(),
            'anomaly_description' => $this->faker->text(),
            'photo_path' => $this->faker->word(),
            'is_anomaly' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'vehicle_check_id' => VehicleCheck::factory(),
            'question_id' => VehicleChecklistQuestion::factory(),
        ];
    }
}
