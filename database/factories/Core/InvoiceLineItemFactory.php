<?php

namespace Database\Factories\Core;

use App\Models\Core\Invoice;
use App\Models\Core\InvoiceLineItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InvoiceLineItemFactory extends Factory
{
    protected $model = InvoiceLineItem::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->text(),
            'type' => $this->faker->word(),
            'quantity' => $this->faker->randomFloat(),
            'unit_price' => $this->faker->randomFloat(),
            'total_price' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'invoice_id' => Invoice::factory(),
        ];
    }
}
