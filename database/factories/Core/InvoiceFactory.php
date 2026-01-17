<?php

namespace Database\Factories\Core;

use App\Enums\Core\InvoiceStatus;
use App\Models\Core\Invoice;
use App\Models\Core\Tenant;
use App\Models\Core\TenantSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $billingStart = Carbon::now()->subMonth();
        $billingEnd = Carbon::now();
        $issuedAt = Carbon::now()->subDays(5);
        $dueAt = $issuedAt->addDays(30);

        return [
            'stripe_invoice_id' => 'in_' . $this->faker->unique()->md5(),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'status' => InvoiceStatus::Pending,
            'billing_period_start' => $billingStart,
            'billing_period_end' => $billingEnd,
            'issued_at' => $issuedAt,
            'due_at' => $dueAt,
            'paid_at' => null,
            'tenant_id' => Tenant::factory(),
            'subscription_id' => TenantSubscription::factory(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Paid,
            'paid_at' => Carbon::now()->subDays(2),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Pending,
            'paid_at' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Pending,
            'due_at' => Carbon::now()->subDays(15),
            'paid_at' => null,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Draft,
            'issued_at' => Carbon::now()->addDays(5),
            'paid_at' => null,
        ]);
    }
}
