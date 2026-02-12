<?php

namespace Database\Factories\Bim;

use App\Models\Bim\BimModel;
use App\Models\Bim\BimObject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BimObjectFactory extends Factory
{
    protected $model = BimObject::class;

    public function definition(): array
    {
        return [
            'guid' => $this->faker->uuid(),
            'ifc_type' => $this->faker->word(),
            'label' => $this->faker->word(),
            'properties' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'bim_model_id' => BimModel::factory(),
        ];
    }
}
