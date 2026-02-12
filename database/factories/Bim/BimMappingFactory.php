<?php

namespace Database\Factories\Bim;

use App\Models\Bim\BimMapping;
use App\Models\Bim\BimObject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BimMappingFactory extends Factory
{
    protected $model = BimMapping::class;

    public function definition(): array
    {
        return [
            'color_override' => $this->faker->word(),
            'metadata' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'bim_object_id' => BimObject::factory(),
        ];
    }
}
