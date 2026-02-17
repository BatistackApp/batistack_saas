<?php

namespace Database\Factories\Articles;

use App\Enums\Articles\SerialNumberStatus;
use App\Models\Articles\Article;
use App\Models\Articles\ArticleSerialNumber;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleSerialNumberFactory extends Factory
{
    protected $model = ArticleSerialNumber::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'article_id' => Article::factory()->serialized(),
            'warehouse_id' => Warehouse::factory(),
            'serial_number' => 'SN-'.strtoupper($this->faker->unique()->bothify('???-####-####')),
            'status' => SerialNumberStatus::InStock,
            'purchase_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'warranty_expiry' => $this->faker->dateTimeBetween('now', '+2 years'),
        ];
    }
}
