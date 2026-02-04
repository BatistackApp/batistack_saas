<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Invoices;
use App\Models\User;
use App\Notifications\Commerce\RetenueGarantieDueNotification;
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
        // 1. Récupération de toutes les factures dont la RG arrive à échéance (J+15)
        // On ne récupère que les factures non libérées.
        $upcomingReleases = Invoices::where('is_retenue_garantie_released', false)
            ->whereNotNull('retenue_garantie_release_date')
            ->where('retenue_garantie_release_date', '<=', now()->addDays(15))
            ->with(['tiers', 'project']) // Eager loading des relations pour la notification
            ->get()
            ->groupBy('tenants_id'); // OPTIMISATION : Groupement par Tenant

        // 2. Traitement par Tenant
        foreach ($upcomingReleases as $tenantId => $invoices) {

            // 3. Récupération des destinataires une seule fois pour TOUT le tenant
            $recipients = User::where('tenants_id', $tenantId)
                ->role(['tenant_admin', 'accountant'])
                ->get();

            if ($recipients->isEmpty()) {
                continue;
            }

            // 4. Envoi des notifications individuelles pour chaque facture
            foreach ($invoices as $invoice) {
                Notification::send($recipients, new RetenueGarantieDueNotification($invoice));
            }
        }
    }
}
