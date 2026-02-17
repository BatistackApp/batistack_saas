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
        $amountTtc = $this->faker->randomFloat(2, 10, 200);
        $taxRate = 20.0;
        $amountHt = round($amountTtc / 1.2, 2);
        $amountTva = round($amountTtc - $amountHt, 2);

        return [
            'expense_report_id' => ExpenseReport::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'project_id' => null, // À lier à un chantier dans les tests
            'date' => $this->faker->date(),
            'description' => $this->faker->sentence(),
            'amount_ht' => $amountHt,
            'tax_rate' => $taxRate,
            'amount_tva' => $amountTva,
            'amount_ttc' => $amountTtc,
            'receipt_path' => null,
            'metadata' => null,
        ];
    }
}
