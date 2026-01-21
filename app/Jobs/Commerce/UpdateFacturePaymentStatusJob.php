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
        $this->facture->load('reglements');

        $montantPaye = $this->facture->reglements->sum('montant');

        if ($this->facture->montant_ttc == 0) {
            return;
        }

        if ($montantPaye == 0) {
            $status = DocumentStatus::Invoiced;
        } elseif ($montantPaye < $this->facture->montant_ttc) {
            $status = DocumentStatus::PartiallyPaid;
        } else {
            $status = DocumentStatus::Paid;
        }

        $this->facture->update([
            'montant_paye' => $montantPaye,
            'status' => $status,
        ]);
    }
}
