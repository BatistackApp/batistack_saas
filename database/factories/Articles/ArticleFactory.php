<?php

namespace Database\Factories\Articles;

use App\Enums\Articles\ArticleUnit;
use App\Models\Articles\Article;
use App\Models\Articles\ArticleCategory;
use App\Models\Core\Tenants;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'sku' => $this->faker->word(),
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'unit' => $this->faker->randomElement(ArticleUnit::cases()),
            'purchase_price_ht' => $this->faker->randomFloat(),
            'cump_ht' => $this->faker->randomFloat(),
            'sale_price_ht' => $this->faker->randomFloat(),
            'min_stock' => $this->faker->randomFloat(),
            'alert_stock' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'category_id' => ArticleCategory::factory(),
            'default_supplier_id' => Tiers::factory(),
        ];
    }
}
