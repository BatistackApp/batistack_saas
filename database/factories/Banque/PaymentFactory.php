<?php

namespace Database\Factories\Banque;

use App\Enums\Banque\BankPaymentMethod;
use App\Models\Banque\BankTransaction;
use App\Models\Banque\Payment;
use App\Models\Commerce\Invoices;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'bank_transaction_id' => BankTransaction::factory(),
            'invoice_id' => Invoices::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 1000),
            'payment_date' => now(),
            'method' => BankPaymentMethod::TransferIncoming,
            'reference' => 'VIR-'.$this->faker->bothify('##??##'),
            'created_by' => User::factory(),
        ];
    }
}
