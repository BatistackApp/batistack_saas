<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\QuoteStatus;
use App\Models\Commerce\Quote;
use DB;
use Exception;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class QuoteService
{
    /**
     * Accepte un devis et prépare le projet pour l'exécution.
     */
    public function acceptQuote(Quote $quote): void
    {
        if ($quote->status !== QuoteStatus::Sent) {
            throw new Exception('Seul un devis envoyé peut être accepté.');
        }

        $quote->update(['status' => QuoteStatus::Accepted]);

        // Optionnel : Déclencher un événement pour informer le module Chantier
        // event(new QuoteAccepted($quote));
    }

    /**
     * DUPLICATION DE DEVIS
     * Copie l'en-tête et l'ensemble des lignes de nomenclature.
     */
    public function duplicateQuote(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote) {
            // 1. Réplication de l'en-tête
            $newQuote = $quote->replicate();

            // On réinitialise les données pour un nouveau brouillon
            $newQuote->reference = $quote->reference . '-COPY'; // L'observer pourra écraser si nécessaire
            $newQuote->status = QuoteStatus::Draft;
            $newQuote->total_ht = $quote->total_ht;
            $newQuote->total_tva = $quote->total_tva;
            $newQuote->total_ttc = $quote->total_ttc;

            $newQuote->save();

            // 2. Réplication des lignes (items)
            foreach ($quote->load('items')->items as $item) {
                $newItem = $item->replicate();
                $newItem->quote_id = $newQuote->id;
                $newItem->save();
            }

            return $newQuote;
        });
    }

    public function generatePdf(Quote $quote): string
    {
        $tenantId = $quote->tenants_id;
        $fileName = "quotes/{$quote->reference}.pdf";
        $directory = "tenants/{$tenantId}/commerce/documents";
        $fullPath = storage_path("app/public/{$directory}/{$fileName}");

        // S'assurer que le dossier existe
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Rendu de la vue Blade en HTML
        $html = View::make('pdf.commerce.quote', [
            'quote' => $quote->load(['customer', 'items', 'project']),
            'tenant' => $quote->tenant
        ])->render();

        // Génération du PDF via Browsershot
        Browsershot::html($html)
            ->setNodeBinary(config('services.browsershot.node_path', '/usr/bin/node'))
            ->setNpmBinary(config('services.browsershot.npm_path', '/usr/bin/npm'))
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->save($fullPath);

        // Mise à jour du chemin dans le modèle (pour la future GED)
        $quote->update(['pdf_path' => "{$directory}/{$fileName}"]);

        return $fullPath;
    }
}
