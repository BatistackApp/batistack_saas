<?php

namespace Database\Factories\Commerce;

use App\Models\Articles\Article;
use App\Models\Commerce\PurchaseOrder;
use App\Models\Commerce\PurchaseOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PurchaseOrderItemFactory extends Factory
{
    protected $model = PurchaseOrderItem::class;

    public function definition(): array
    {
        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'article_id' => Article::factory(),
            'description' => $this->faker->words(3, true),
            'quantity' => $this->faker->randomFloat(3, 1, 100),
            'received_quantity' => 0,
            'unit_price_ht' => $this->faker->randomFloat(2, 5, 500),
            'tax_rate' => 20.00,
        ];
    }
}
