<?php

namespace App\Services\Expense;

use App\Models\Expense\ExpenseItem;
use App\Models\Expense\ExpenseReport;

class ChantierImputationService
{
    public function imputeReportToChantiers(ExpenseReport $report): void
    {
        foreach ($report->items as $item) {
            if ($item->chantier_id) {
                $this->imputeItem($item);
            }
        }
    }

    protected function imputeItem(ExpenseItem $item): void
    {
        $chantier = $item->project;

        if (! $chantier) {
            return;
        }

        // Logique métier d'imputation analytique ici
    }
}
