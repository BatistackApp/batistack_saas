<?php

namespace Database\Factories\Commerce;

use App\Models\Commerce\InvoiceItem;
use App\Models\Commerce\Invoices;
use App\Models\Commerce\QuoteItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->word(),
            'quantity' => $this->faker->randomFloat(),
            'unit_price_ht' => $this->faker->randomFloat(),
            'tax_rate' => $this->faker->randomFloat(),
            'progress_percentage' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'invoices_id' => Invoices::factory(),
            'quote_item_id' => QuoteItem::factory(),
        ];
    }
}
