<?php

namespace App\Jobs\Tiers;

use App\Enums\Tiers\TierDocumentStatus;
use App\Models\Tiers\TierDocument;
use App\Models\Tiers\Tiers;
use App\Notifications\Tiers\DocumentExpirationNotification;
use App\Services\Tiers\TierComplianceService;
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
        TierDocument::where('expires_at', '<', now())
            ->where('status', '!=', TierDocumentStatus::Expired)
            ->update(['status' => TierDocumentStatus::Expired]);

        // 2. Notification proactive (30 jours avant)
        $expiringDocs = TierDocument::with('tier')
            ->where('expires_at', '<=', now()->addDays(30))
            ->where('status', '!=', TierDocumentStatus::Expired)
            ->get();

        $expiringDocs->groupBy('tiers_id')->each(function ($docs, $tierId) {
            $tier = $docs->first()->tier;
            // Passez la collection de documents à la notification
            $tier->notify(new DocumentExpirationNotification($tier, $docs));
        });
    }
}
