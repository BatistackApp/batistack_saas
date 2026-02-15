<?php

namespace App\Jobs\Fleet;

use App\Models\Core\Tenants;
use App\Services\Fleet\AntaiExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ExportAntaiFinesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Ce Job parcourt chaque entreprise (Tenant) pour automatiser l'export.
     */
    public function handle(AntaiExportService $antaiService): void
    {
        Tenants::all()->each(function (Tenants $tenant) use ($antaiService) {
            // Récupération des amendes prêtes pour ce tenant spécifique
            $fines = $antaiService->getPendingFinesForExport($tenant->id);

            if ($fines->isNotEmpty()) {
                try {
                    $filePath = $antaiService->generateCsv($fines, $tenant->id);

                    Log::info("Export ANTAI automatique réussi pour le Tenant #{$tenant->id}", [
                        'count' => $fines->count(),
                        'file' => $filePath
                    ]);

                    // Note : On pourrait ajouter ici l'envoi d'une notification
                    // au gestionnaire pour l'informer que le fichier est prêt.

                } catch (\Exception $e) {
                    Log::error("Échec de l'export ANTAI automatique pour le Tenant #{$tenant->id}: " . $e->getMessage());
                }
            }
        });
    }
}
