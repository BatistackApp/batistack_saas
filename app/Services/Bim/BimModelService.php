<?php

namespace App\Services\Bim;

use App\Enums\Bim\BimModelStatus;
use App\Models\Bim\BimModel;
use App\Models\Bim\BimObject;
use DB;
use Storage;

class BimModelService
{
    /**
     * Initialise le traitement d'une nouvelle maquette.
     */
    public function processNewModel(BimModel $model): void
    {
        $model->update(['status' => BimModelStatus::PROCESSING]);

        // Note : En production, cela déclencherait un Job asynchrone
        // ou un service Node.js/Wasm pour parser le fichier IFC lourd.
        // Ici, nous préparons la structure pour recevoir les données extraites.
    }

    /**
     * Enregistre les objets extraits du fichier IFC après parsing.
     * Appelé par le parseur une fois la géométrie analysée.
     */
    public function ingestExtractedObjects(BimModel $model, array $extractedObjects): void
    {
        DB::transaction(function () use ($model, $extractedObjects) {
            foreach ($extractedObjects as $objData) {
                BimObject::updateOrCreate(
                    [
                        'bim_model_id' => $model->id,
                        'guid' => $objData['guid'],
                    ],
                    [
                        'ifc_type' => $objData['ifc_type'],
                        'label' => $objData['label'] ?? null,
                        'properties' => $objData['properties'] ?? [],
                    ]
                );
            }

            $model->update(['status' => BimModelStatus::READY]);
        });
    }

    /**
     * Génère une URL signée temporaire pour que le frontend (Three.js)
     * puisse télécharger le fichier IFC en toute sécurité.
     */
    public function getViewerUrl(BimModel $model): string
    {
        return Storage::disk('public')->temporaryUrl(
            $model->file_path,
            now()->addMinutes(60)
        );
    }
}
