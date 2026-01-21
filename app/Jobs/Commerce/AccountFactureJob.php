<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Facture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AccountFactureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Facture $facture) {}

    public function handle(): void
    {
        // Créer les écritures comptables
        // À intégrer avec le module Comptabilité
        // Enregistrement: Ventes/Clients, TVA, Produits
    }
}
