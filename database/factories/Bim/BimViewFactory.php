<?php

namespace Database\Factories\Bim;

use App\Models\Bim\BimModel;
use App\Models\Bim\BimView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BimViewFactory extends Factory
{
    protected $model = BimView::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'camera_state' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'bim_model_id' => BimModel::factory(),
            'user_id' => User::factory(),
        ];
    }
}
