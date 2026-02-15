<?php

namespace Database\Factories\Fleet;

use App\Models\Fleet\FineCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FineCategoryFactory extends Factory
{
    protected $model = FineCategory::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->word(),
            'antai_code' => $this->faker->word(),
            'default_amount' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
