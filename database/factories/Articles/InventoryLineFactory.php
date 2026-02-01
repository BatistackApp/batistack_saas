<?php

namespace Database\Factories\Articles;

use App\Models\Articles\Article;
use App\Models\Articles\InventoryLine;
use App\Models\Articles\InventorySession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InventoryLineFactory extends Factory
{
    protected $model = InventoryLine::class;

    public function definition(): array
    {
        return [
            'theoretical_quantity' => $this->faker->randomFloat(),
            'counted_quantity' => $this->faker->randomFloat(),
            'difference' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'inventory_session_id' => InventorySession::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
