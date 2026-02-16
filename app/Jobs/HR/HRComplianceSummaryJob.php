<?php

namespace App\Jobs\HR;

use App\Models\Core\Tenants;
use App\Models\HR\EmployeeSkill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class HRComplianceSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Tenants::all()->each(function ($tenant) {
            // On récupère tout ce qui périme dans moins de 15 jours ou déjà périmé
            $issues = EmployeeSkill::whereHas('employee', fn ($q) => $q->where('tenants_id', $tenant->id))
                ->whereDate('expiry_date', '<=', now()->addDays(15))
                ->with(['employee', 'skill'])
                ->get();

            if ($issues->isNotEmpty()) {
                // Notifier les admins du tenant
                $admins = $tenant->users()->role('tenant_admin')->get();
                Notification::send($admins, new DailyComplianceReportNotification($issues));
            }
        });
    }
}
