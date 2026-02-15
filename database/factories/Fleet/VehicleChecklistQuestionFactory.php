<?php

namespace Database\Factories\Fleet;

use App\Models\Fleet\VehicleChecklistQuestion;
use App\Models\Fleet\VehicleChecklistTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleChecklistQuestionFactory extends Factory
{
    protected $model = VehicleChecklistQuestion::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->word(),
            'description' => $this->faker->text(),
            'response_type' => $this->faker->word(),
            'is_mandatory' => $this->faker->boolean(),
            'requires_photo_on_anomaly' => $this->faker->boolean(),
            'sort_order' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'template_id' => VehicleChecklistTemplate::factory(),
        ];
    }
}
