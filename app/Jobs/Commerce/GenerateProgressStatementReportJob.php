<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Invoices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GenerateProgressStatementReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Invoices $invoice) {}

    public function handle(): void
    {
        // Ce job génère le document "État de Situation" qui est un tableau comparatif :
        // [Item Devis] | [Qté Totale] | [Cumul Précédent %] | [Mois %] | [Cumul Total %]

        Log::info("Génération du rapport de situation n°{$this->invoice->situation_number} pour {$this->invoice->reference}.");

        // Stockage du document dans le module GED (Gestion Électronique de Documents)
    }
}
