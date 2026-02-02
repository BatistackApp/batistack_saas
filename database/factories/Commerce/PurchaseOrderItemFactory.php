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
            'description' => $this->faker->text(),
            'quantity' => $this->faker->randomFloat(),
            'received_quantity' => $this->faker->randomFloat(),
            'unit_price_ht' => $this->faker->randomFloat(),
            'tax_rate' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'purchase_order_id' => PurchaseOrder::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
