<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Invoices;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class CheckRetenueGarantieReleaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $upcomingReleases = Invoices::where('is_retenue_garantie_released', false)
            ->whereNotNull('retenue_garantie_release_date')
            ->where('retenue_garantie_release_date', '<=', now()->addDays(15))
            ->with(['tenant', 'tiers', 'project'])
            ->get();

        foreach ($upcomingReleases as $invoice) {
            User::where('tenants_id', $invoice->tenants_id)
                ->role(['tenant_admin', 'accountant'])
                ->get()
                ->each(function ($user) use ($upcomingReleases) {
                    Notification::send($user, new RetenueGarantieReleaseNotification($upcomingReleases));
                });
        }
    }
}
