<?php

namespace Database\Factories\Articles;

use App\Models\Articles\Article;
use App\Models\Articles\Stock;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'quantity' => $this->faker->randomFloat(),
            'reserved_quantity' => $this->faker->randomFloat(),
            'average_unit_cost' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'article_id' => Article::factory(),
            'warehouse_id' => Warehouse::factory(),
        ];
    }
}
