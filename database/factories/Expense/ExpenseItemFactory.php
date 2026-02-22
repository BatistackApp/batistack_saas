<?php

namespace Database\Factories\Expense;

use App\Models\Expense\ExpenseCategory;
use App\Models\Expense\ExpenseItem;
use App\Models\Expense\ExpenseReport;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'project_id' => Project::factory(), // À lier à un chantier dans les tests
            'project_phase_id' => function (array $attributes) {
                return ProjectPhase::where('project_id', $attributes['project_id'])->first()?->id
                    ?? ProjectPhase::factory(['project_id' => $attributes['project_id']]);
            },
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
