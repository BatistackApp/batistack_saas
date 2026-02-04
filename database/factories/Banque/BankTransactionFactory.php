<?php

namespace Database\Factories\Banque;

use App\Enums\Banque\BankTransactionType;
use App\Models\Banque\BankAccount;
use App\Models\Banque\BankTransaction;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankTransactionFactory extends Factory
{
    protected $model = BankTransaction::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, -5000, 5000);

        return [
            'tenants_id' => Tenants::factory(),
            'bank_account_id' => BankAccount::factory(),
            'value_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'label' => $this->faker->randomElement([
                'VIR SEPA '.$this->faker->company(),
                'PAIEMENT CB '.$this->faker->city(),
                'REMISE CHEQUE '.$this->faker->randomNumber(5),
                'PRLV URSSAF',
            ]),
            'amount' => $amount,
            'type' => $amount > 0 ? BankTransactionType::Credit : BankTransactionType::Debit,
            'external_id' => 'bridge_'.$this->faker->unique()->sha1(),
            'is_reconciled' => false,
            'raw_metadata' => ['category' => 'BTP', 'provider' => 'Bridge'],
        ];
    }
}
