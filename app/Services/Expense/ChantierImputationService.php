<?php

namespace App\Services\Expense;

use App\Models\Expense\ExpenseItem;
use App\Models\Expense\ExpenseReport;
use Illuminate\Support\Facades\DB;

class ChantierImputationService
{
    public function imputeReportToChantiers(ExpenseReport $report): void
    {
        DB::transaction(function () use ($report) {
            foreach ($report->items as $item) {
                if ($item->project_id) {
                    $this->imputeItem($item);
                }
            }
        });
    }

    protected function imputeItem(ExpenseItem $item): void
    {
        $amount = (float) $item->amount_ht;

        // 1. Imputation sur le Projet global
        if ($item->project) {
            $item->project->increment('total_costs', $amount);
        }

        // 2. Imputation fine sur la Phase (Lot/Zone) - Recommandation 1
        if ($item->phase) {
            // On incrémente le coût réel de la phase spécifique
            $item->phase->increment('real_cost', $amount);
        }

        // 3. Marquage pour refacturation si nécessaire - Recommandation 3
        if ($item->is_billable) {
            $this->flagForBilling($item);
        }
    }

    /**
     * Prépare le frais pour être inclus dans la prochaine facture client.
     */
    protected function flagForBilling(ExpenseItem $item): void
    {
        // Logique pour insérer dans une table de pivot ou envoyer vers le module Commerce
        // Ex: $item->project->unbilledItems()->create([...]);
    }
}
