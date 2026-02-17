<?php

namespace Database\Factories\Commerce;

use App\Models\Commerce\InvoiceItem;
use App\Models\Commerce\Invoices;
use App\Models\Commerce\QuoteItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        return [
            'invoices_id' => Invoices::factory(),
            'quote_item_id' => QuoteItem::factory(),
            'label' => $this->faker->sentence(4),
            'quantity' => $this->faker->randomFloat(3, 1, 10),
            'unit_price_ht' => $this->faker->randomFloat(2, 50, 200),
            'tax_rate' => 20.00,
            'progress_percentage' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
