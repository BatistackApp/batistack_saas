<?php

namespace App\Console\Commands\Payroll;

use App\Enums\HR\TimeEntryStatus;
use App\Models\HR\TimeEntry;
use App\Notifications\Payroll\PendingPayrollApprovalsNotification;
use Illuminate\Console\Command;

class RemindPendingTimesheetsCommand extends Command
{
    protected $signature = 'payroll:remind-approvals';

    protected $description = 'Relance les managers pour les pointages non approuvés avant la clôture de paie';

    public function handle(): void
    {
        // On cherche les pointages du mois en cours qui ne sont pas encore 'Approved'
        $startOfMonth = now()->startOfMonth();

        $pendingEntries = TimeEntry::where('date', '>=', $startOfMonth)
            ->where('status', '!=', TimeEntryStatus::Approved)
            ->with('employee.manager')
            ->get();

        $managersToNotify = $pendingEntries->map(fn($e) => $e->employee->manager)->filter()->unique('id');

        foreach ($managersToNotify as $manager) {
            // Notification personnalisée (non fournie mais à prévoir)
            $manager->notify(new PendingPayrollApprovalsNotification($pendingEntries->count(), $startOfMonth->format('F Y')));
            $this->info("Relance envoyée au manager : {$manager->name}");
        }
    }
}
