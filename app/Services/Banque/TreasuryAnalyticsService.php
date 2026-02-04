<?php

namespace App\Services\Banque;

use App\Enums\Banque\BankTransactionType;
use App\Models\Banque\Payment;
use App\Models\Projects\Project;

class TreasuryAnalyticsService
{
    public function getProjectSummary(Project $project): array
    {
        $incomings = Payment::whereHas('invoice', fn ($q) => $q->where('project_id', $project->id))
            ->whereHas('bankTransaction', fn ($q) => $q->where('type', BankTransactionType::Credit))
            ->sum('amount');

        $outgoings = Payment::whereHas('invoice', fn ($q) => $q->where('project_id', $project->id))
            ->whereHas('bankTransaction', fn ($q) => $q->where('type', BankTransactionType::Debit))
            ->sum('amount');

        return [
            'project_id' => $project->id,
            'project_code' => $project->code_project,
            'real_cash_in' => (float) $incomings,
            'real_cash_out' => (float) $outgoings,
            'net_cash_flow' => (float) ($incomings - $outgoings),
            'invoiced_not_paid' => (float) ($project->invoices()->sum('total_ht') - $incomings),
        ];
    }
}
