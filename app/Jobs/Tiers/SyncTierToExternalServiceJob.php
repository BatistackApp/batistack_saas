<?php

namespace App\Jobs\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTierToExternalServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Tiers $tiers) {}

    public function handle(): void
    {
        // Synchronisation avec services externes (API tiers, CRM, etc.)
        // À implémenter selon les besoins
    }
}
