<?php

namespace App\Jobs\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckTierDocumentExpirationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle(): void
    {
        // Récupération des documents proches de l'expiration (GED)
        $tiersWithExpiringDocs = Tiers::query()
            ->with('tenant')
            ->get()
            ->filter(function (Tiers $tier) {
                // Logique de vérification via GED
                // À adapter selon implémentation GED
                return false;
            });

        $tiersWithExpiringDocs->each(function (Tiers $tier) {
            $tier->notify(new DocumentExpirationNotification($tier));
        });
    }
}
