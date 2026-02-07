<?php

namespace Database\Factories\GPAO;

use App\Models\Articles\Article;
use App\Models\GPAO\WorkOrder;
use App\Models\GPAO\WorkOrderComponent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class WorkOrderComponentFactory extends Factory
{
    protected $model = WorkOrderComponent::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->word(),
            'quantity_planned' => $this->faker->randomFloat(),
            'quantity_consumed' => $this->faker->randomFloat(),
            'unit_cost_ht' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'work_order_id' => WorkOrder::factory(),
            'article_id' => Article::factory(),
        ];
    }
}
