<?php

namespace App\Services\Fleet;

use App\Enums\Fleet\DesignationStatus;
use App\Models\Fleet\VehicleFine;
use Illuminate\Database\Eloquent\Collection;
use League\Csv\Writer;
use SplTempFileObject;
use Storage;

class AntaiExportService
{
    /**
     * Génère le fichier CSV au format attendu par l'ANTAI.
     * Le format requiert des colonnes spécifiques et un encodage précis.
     */
    public function generateCsv(Collection $fines, int $tenantId): string
    {
        // Création du document CSV en mémoire
        $csv = Writer::createFromFileObject(new SplTempFileObject);
        $csv->setDelimiter(';'); // L'ANTAI utilise souvent le point-virgule

        // Entêtes réglementaires ANTAI (exemple type)
        $csv->insertOne([
            'NumeroAvis',
            'DateInfraction',
            'Immatriculation',
            'PaysImmatriculation',
            'NomConducteur',
            'PrenomConducteur',
            'DateNaissance',
            'VilleNaissance',
            'Adresse',
            'CodePostal',
            'Ville',
            'Pays',
            'NumeroPermis',
            'PaysPermis',
        ]);

        foreach ($fines as $fine) {
            $driver = $fine->driver; // Relation avec le modèle User/Employee

            $csv->insertOne([
                $fine->notice_number,
                $fine->offense_at->format('d/m/Y'),
                $fine->vehicle->license_plate,
                'FR', // Par défaut
                $driver?->last_name,
                $driver?->first_name,
                $driver?->birth_date?->format('d/m/Y') ?? '',
                $driver?->birth_city ?? '',
                $driver?->address ?? '',
                $driver?->zip_code ?? '',
                $driver?->city ?? '',
                'FR',
                $driver?->license_number ?? '',
                'FR',
            ]);

            // Mise à jour du statut de la contravention
            $fine->update([
                'designation_status' => DesignationStatus::Exported,
                'exported_at' => now(),
            ]);
        }

        // Sauvegarde du fichier sur le disque sécurisé
        $filename = 'tenant/'.$tenantId.'/antai/export_'.now()->format('Ymd_His').'.csv';
        Storage::disk('public')->put($filename, $csv->toString());

        return $filename;
    }

    /**
     * Liste les contraventions prêtes pour un nouvel export.
     */
    public function getPendingFinesForExport(int $tenantId): Collection
    {
        return VehicleFine::where('tenants_id', $tenantId)
            ->where('designation_status', DesignationStatus::Pending)
            ->whereNotNull('user_id') // On ne peut exporter que si un chauffeur est lié
            ->with(['vehicle', 'driver'])
            ->get();
    }
}
