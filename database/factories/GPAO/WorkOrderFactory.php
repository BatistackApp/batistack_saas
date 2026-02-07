<?php

namespace Database\Factories\GPAO;

use App\Enums\GPAO\WorkOrderStatus;
use App\Models\Articles\Ouvrage;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\GPAO\WorkOrder;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    public function definition(): array
    {
        return [
            'reference' => $this->faker->word(),
            'quantity_planned' => $this->faker->randomFloat(),
            'quantity_produced' => $this->faker->randomFloat(),
            'status' => $this->faker->randomElement(WorkOrderStatus::cases()),
            'priority' => $this->faker->randomNumber(),
            'planned_start_at' => Carbon::now(),
            'planned_end_at' => Carbon::now(),
            'actual_start_at' => Carbon::now(),
            'actuel_end_at' => Carbon::now(),
            'total_cost_ht' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'ouvrage_id' => Ouvrage::factory(),
            'warehouse_id' => Warehouse::factory(),
            'project_id' => Project::factory(),
            'project_phase_id' => ProjectPhase::factory(),
        ];
    }
}
