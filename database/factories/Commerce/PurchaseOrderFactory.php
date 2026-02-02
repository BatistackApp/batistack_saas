<?php

namespace Database\Factories\Commerce;

use App\Models\Commerce\PurchaseOrder;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'reference' => $this->faker->word(),
            'status' => $this->faker->word(),
            'order_date' => Carbon::now(),
            'expected_delivery_date' => Carbon::now(),
            'total_ht' => $this->faker->randomFloat(),
            'total_tva' => $this->faker->randomFloat(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'supplier_id' => Tiers::factory(),
            'project_id' => Project::factory(),
            'created_by' => User::factory(),
        ];
    }
}
