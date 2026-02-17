<?php

namespace App\Console\Commands\Expense;

use App\Enums\Expense\ExpenseStatus;
use App\Models\Expense\ExpenseReport;
use App\Models\User;
use App\Notifications\Expense\ExpenseSubmittedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class RemindPendingExpensesCommand extends Command
{
    protected $signature = 'expense:remind-pending';

    protected $description = 'Relance les validateurs pour les notes de frais en attente depuis plus de 3 jours';

    public function handle(): void
    {
        $pendingReports = ExpenseReport::where('status', ExpenseStatus::Submitted)
            ->where('submitted_at', '<=', now()->subDays(3))
            ->get();

        if ($pendingReports->isEmpty()) {
            $this->info('Aucune note de frais en attente de relance.');

            return;
        }

        $validators = User::permission('validate-expenses')->get();

        foreach ($pendingReports as $report) {
            Notification::send($validators, new ExpenseSubmittedNotification($report));
            $this->info("Relance envoyée pour la note : {$report->label}");
        }
    }
}
