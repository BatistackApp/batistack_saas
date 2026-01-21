<?php

namespace App\Jobs\Commerce;

use App\Enums\Commerce\DocumentStatus;
use App\Models\Commerce\Facture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateFacturePaymentStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Facture $facture) {}

    public function handle(): void
    {
        $montantTtc = $this->facture->montant_ttc;
        $montantPaye = $this->facture->reglements()->sum('montant');

        $status = match (true) {
            $montantPaye == 0 => DocumentStatus::Invoiced,
            $montantPaye < $montantTtc => DocumentStatus::PartiallyPaid,
            $montantPaye >= $montantTtc => DocumentStatus::Paid,
        };

        $this->facture->update(['status' => $status]);
    }
}
