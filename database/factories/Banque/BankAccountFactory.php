<?php

namespace Database\Factories\Banque;

use App\Enums\Banque\BankAccountType;
use App\Enums\Banque\BankSyncStatus;
use App\Models\Banque\BankAccount;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankAccountFactory extends Factory
{
    protected $model = BankAccount::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'name' => $this->faker->randomElement(['Compte Courant BNP', 'LCL Professionnel', 'Caisse Chantier']),
            'bank_name' => $this->faker->company(),
            'iban' => $this->faker->iban('FR'),
            'type' => BankAccountType::Current,
            'sync_status' => BankSyncStatus::Active,
            'bridge_id' => (string) $this->faker->randomNumber(8),
            'bridge_item_id' => (string) $this->faker->randomNumber(6),
            'last_synced_at' => now(),
            'initial_balance' => 1000,
            'current_balance' => 1000,
            'is_active' => true,
        ];
    }
}
