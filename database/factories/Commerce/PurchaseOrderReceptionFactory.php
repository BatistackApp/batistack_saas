<?php

namespace Database\Factories\Commerce;

use App\Models\Commerce\PurchaseOrderItem;
use App\Models\Commerce\PurchaseOrderReception;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PurchaseOrderReceptionFactory extends Factory
{
    protected $model = PurchaseOrderReception::class;

    public function definition(): array
    {
        return [
            'quantity' => $this->faker->randomFloat(),
            'delivery_note_ref' => $this->faker->word(),
            'received_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'purchase_order_item_id' => PurchaseOrderItem::factory(),
            'created_by' => User::factory(),
        ];
    }
}
