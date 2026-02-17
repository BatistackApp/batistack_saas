<?php

namespace Database\Factories\HR;

use App\Enums\HR\ContractType;
use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(ContractType::cases());

        return [
            'tenants_id' => Tenants::factory(),
            'user_id' => User::factory(),
            'external_id' => $this->faker->unique()->bothify('PAY-#####'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'job_title' => $this->faker->randomElement(['Chef de chantier', 'Maçon', 'Conducteur d\'engins', 'Électricien']),
            'department' => $this->faker->randomElement(['Gros Œuvre', 'Second Œuvre', 'Logistique', 'Bureau d\'études']),
            'hourly_cost_charged' => $this->faker->randomFloat(2, 35, 75),
            'contract_type' => $type,
            'contract_end_date' => $type !== ContractType::CDI ? $this->faker->dateTimeBetween('+1 month', '+1 year') : null,
            'hired_at' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'is_active' => true,
        ];
    }
}
