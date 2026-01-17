<?php

namespace Database\Factories\Core;

use App\Enums\Core\InvoiceLineItemType;
use App\Models\Core\Invoice;
use App\Models\Core\InvoiceLineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceLineItemFactory extends Factory
{
    protected $model = InvoiceLineItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 100);
        $unitPrice = $this->faker->randomFloat(2, 10, 500);
        $totalPrice = (string) ($quantity * $unitPrice);

        return [
            'description' => $this->faker->sentence(),
            'type' => InvoiceLineItemType::Plan,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'invoice_id' => Invoice::factory(),
        ];
    }

    public function plan(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InvoiceLineItemType::Plan,
        ]);
    }

    public function module(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InvoiceLineItemType::Module,
        ]);
    }

    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InvoiceLineItemType::Credit,
        ]);
    }

    public function adjustment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => InvoiceLineItemType::Adjustment,
        ]);
    }
}
