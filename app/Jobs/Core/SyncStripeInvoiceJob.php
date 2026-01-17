<?php

namespace App\Jobs\Core;

use App\Models\Core\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncStripeInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function handle(): void
    {
        // appeler l'API Stripe / mettre à jour le modèle local selon le payload
        // Exemple: fetch Stripe invoice, enrichir, sauvegarder
    }
}
