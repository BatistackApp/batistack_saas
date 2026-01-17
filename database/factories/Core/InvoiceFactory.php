<?php

namespace Database\Factories\Core;

use App\Enums\Core\InvoiceStatus;
use App\Models\Core\Invoice;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'stripe_invoice_id' => $this->faker->word(),
            'amount' => $this->faker->randomFloat(),
            'status' => InvoiceStatus::cases()[array_rand(InvoiceStatus::cases())],
            'billing_period_start' => Carbon::now(),
            'billing_period_end' => Carbon::now(),
            'issued_at' => Carbon::now(),
            'due_at' => Carbon::now(),
            'paid_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
        ];
    }
}
