<?php

namespace App\Jobs\Core;

use App\Enums\Core\TenantStatus;
use App\Models\Core\Tenants;
use App\Services\Core\BillingHistoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class CheckOverdueInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes

    public function __construct(
        private BillingHistoryService $historyService,
    )
    {
    }

    public function handle(): void
    {
        try {
            Log::info("Checking overdue invoices...");

            $overdueThreshold = now()->subDays(30);

            $tenantsWithOverdueInvoices = Tenants::whereHas('subscriptions', function ($q) use ($overdueThreshold) {
                $q->where('stripe_status', 'past_due')
                    ->orWhere(function ($q) use ($overdueThreshold) {
                        $q->where('created_at', '<', $overdueThreshold);
                    });
            })->get();

            foreach ($tenantsWithOverdueInvoices as $tenant) {
                if ($tenant->status === TenantStatus::Active->value) {
                    $tenant->update(['status' => TenantStatus::Suspended->value]);

                    Log::warning("Tenant suspended due to overdue invoices", [
                        'tenant_id' => $tenant->id,
                    ]);

                    dispatch(new SendTenantSuspensionNotificationJob($tenant->id));

                    $this->historyService->logEvent(
                        tenant: $tenant,
                        eventType: 'tenant_suspended_overdue',
                        description: "Tenant suspendu pour non-paiement",
                    );
                }
            }

            Log::info("Overdue invoices check completed. Tenants suspended: {$tenantsWithOverdueInvoices->count()}");
        } catch (\Exception $e) {
            Log::error("Overdue invoices check failed", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
