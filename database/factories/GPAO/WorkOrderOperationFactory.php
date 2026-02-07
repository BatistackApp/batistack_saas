<?php

namespace Database\Factories\GPAO;

use App\Models\GPAO\WorkCenter;
use App\Models\GPAO\WorkOrder;
use App\Models\GPAO\WorkOrderOperation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class WorkOrderOperationFactory extends Factory
{
    protected $model = WorkOrderOperation::class;

    public function definition(): array
    {
        return [
            'sequence' => $this->faker->randomNumber(),
            'label' => $this->faker->word(),
            'time_planned_minutes' => $this->faker->randomFloat(),
            'time_actual_minutes' => $this->faker->randomFloat(),
            'status' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'work_order_id' => WorkOrder::factory(),
            'work_center_id' => WorkCenter::factory(),
        ];
    }
}
