<?php

namespace Database\Factories\Articles;

use App\Models\Articles\Article;
use App\Models\Articles\ArticleCategory;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->text(),
            'code' => $this->faker->word(),
            'barcode' => $this->faker->word(),
            'sku' => $this->faker->word(),
            'type' => $this->faker->word(),
            'unit_of_measure' => $this->faker->word(),
            'weight_kg' => $this->faker->randomFloat(),
            'volume_m3' => $this->faker->randomFloat(),
            'purchase_price' => $this->faker->randomFloat(),
            'selling_price' => $this->faker->randomFloat(),
            'margin_percentage' => $this->faker->randomFloat(),
            'vat_rate' => $this->faker->word(),
            'external_reference' => $this->faker->word(),
            'is_active' => $this->faker->boolean(),
            'requires_lot_tracking' => $this->faker->boolean(),
            'requires_serial_number' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'article_category_id' => ArticleCategory::factory(),
        ];
    }
}
