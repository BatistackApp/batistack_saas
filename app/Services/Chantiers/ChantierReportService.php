<?php

namespace App\Services\Chantiers;

use App\Models\Chantiers\Chantier;

class ChantierReportService
{
    public function generateProfitabilityReport(Chantier $chantier): array
    {
        $costs = $chantier->costs()
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->keyBy('category');

        $budgets = $chantier->budgets()
            ->get()
            ->keyBy('category');

        $report = [];

        foreach ($budgets as $category => $budget) {
            $spent = $costs->get($category)?->total ?? 0;

            $report[] = [
                'category' => $category,
                'planned' => $budget->planned_amount,
                'spent' => $spent,
                'remaining' => $budget->planned_amount - $spent,
                'usage_percent' => $budget->planned_amount > 0
                    ? ($spent / $budget->planned_amount) * 100
                    : 0,
            ];
        }

        return $report;
    }

    public function exportCostsToCSV(Chantier $chantier): string
    {
        $costs = $chantier->costs()
            ->orderBy('cost_date')
            ->get(['cost_date', 'category', 'label', 'amount', 'reference']);

        $csv = "Date,Catégorie,Libellé,Montant,Référence\n";

        foreach ($costs as $cost) {
            $csv .= implode(',', [
                $cost->cost_date->format('Y-m-d'),
                $cost->category->label(),
                $cost->label,
                $cost->amount,
                $cost->reference ?? '',
            ])."\n";
        }

        return $csv;
    }
}
