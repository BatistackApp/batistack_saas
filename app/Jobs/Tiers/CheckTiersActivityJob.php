<?php

namespace App\Jobs\Tiers;

use App\Enums\Tiers\TierStatus;
use App\Models\Tiers\Tiers;
use App\Services\SirenService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckTiersActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle(SirenService $service): void
    {
        Tiers::whereNotNull('siret')
            ->where('status', '!=', TierStatus::Archived->value)
            ->chunk(100, function ($tiers) use ($service) {
                foreach ($tiers as $tier) {
                    if (! $tier->siret) {
                        continue;
                    }

                    $data = $service->fetchCompanyData($tier->siret);

                    if (! $service->isStillActive($tier->siret)) {
                        $tier->update(['status' => TierStatus::Inactive->value]);
                        // Notification au service achat/admin ici
                    }
                }
            });
    }
}
