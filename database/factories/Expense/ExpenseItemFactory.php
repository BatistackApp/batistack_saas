<?php

namespace Database\Factories\Expense;

use App\Models\Expense\ExpenseCategory;
use App\Models\Expense\ExpenseItem;
use App\Models\Expense\ExpenseReport;
use App\Models\Projects\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ExpenseItemFactory extends Factory
{
    protected $model = ExpenseItem::class;

    public function definition(): array
    {
        return [
            'date' => Carbon::now(),
            'description' => $this->faker->text(),
            'amount_ht' => $this->faker->randomFloat(),
            'tax_rate' => $this->faker->randomFloat(),
            'amount_tva' => $this->faker->randomFloat(),
            'amount_ttc' => $this->faker->randomFloat(),
            'receipt_path' => $this->faker->word(),
            'metadata' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'expense_report_id' => ExpenseReport::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'project_id' => Project::factory(),
        ];
    }
}
