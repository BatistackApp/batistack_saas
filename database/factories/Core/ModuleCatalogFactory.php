<?php

namespace Database\Factories\Core;

use App\Models\Core\ModuleCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ModuleCatalogFactory extends Factory
{
    protected $model = ModuleCatalog::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->text(),
            'is_core' => $this->faker->boolean(),
            'price_monthly' => $this->faker->randomFloat(),
            'price_yearly' => $this->faker->randomFloat(),
            'sort_order' => $this->faker->randomNumber(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
