<?php

namespace Database\Factories\Articles;

use App\Enums\Articles\ArticleUnit;
use App\Enums\Articles\TrackingType;
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
            'tenants_id' => Tenants::factory(),
            'category_id' => ArticleCategory::factory(),
            'default_supplier_id' => Tiers::factory(),
            'sku' => 'ART-' . $this->faker->unique()->bothify('??-####'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'unit' => $this->faker->randomElement(ArticleUnit::cases()),
            'tracking_type' => TrackingType::Quantity, // Par défaut, peut être surchargé à SN
            'barcode' => $this->faker->ean13(),
            'qr_code_base' => $this->faker->uuid(),
            'poids' => $this->faker->randomFloat(3, 0.1, 50),
            'volume' => $this->faker->randomFloat(3, 0.01, 5),
            'purchase_price_ht' => $this->faker->randomFloat(2, 10, 500),
            'cump_ht' => $this->faker->randomFloat(2, 10, 500),
            'sale_price_ht' => $this->faker->randomFloat(2, 20, 800),
            'min_stock' => $this->faker->numberBetween(5, 20),
            'alert_stock' => $this->faker->numberBetween(10, 30),
            'total_stock' => 0, // Géré par les mouvements et l'observer
        ];
    }

    /**
     * État pour un article suivi par numéro de série.
     */
    public function serialized(): static
    {
        return $this->state(fn (array $attributes) => [
            'tracking_type' => TrackingType::SerialNumber,
        ]);
    }
}
