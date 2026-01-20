<?php

namespace Database\Factories\Articles;

use App\Models\Articles\Article;
use App\Models\Articles\StockMouvement;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StockMouvementFactory extends Factory
{
    protected $model = StockMouvement::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'reason' => $this->faker->word(),
            'quantity' => $this->faker->randomFloat(),
            'reference' => $this->faker->word(),
            'notes' => $this->faker->word(),
            'mouvement_date' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'article_id' => Article::factory(),
            'warehouse_id' => Warehouse::factory(),
            'created_by' => User::factory(),
        ];
    }
}
