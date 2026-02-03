<?php

namespace Database\Factories\Commerce;

use App\Enums\Commerce\PurchaseOrderStatus;
use App\Models\Commerce\PurchaseOrder;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'supplier_id' => Tiers::factory(),
            'project_id' => Project::factory(),
            'reference' => 'BC-'.$this->faker->unique()->numberBetween(10000, 99999),
            'status' => PurchaseOrderStatus::Draft,
            'order_date' => now(),
            'expected_delivery_date' => now()->addDays(7),
            'total_ht' => 0, // Calculé dynamiquement via les items
            'total_tva' => 0,
            'notes' => $this->faker->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
