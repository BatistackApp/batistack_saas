<?php

namespace App\Services\GED;

use App\Models\Core\Tenants;
use App\Models\GED\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GEDService
{
    public function __construct(
        protected QuotaService $quotaService
    ) {}

    /**
     * Gère l'upload complet d'un document
     */
    public function uploadDocument(UploadedFile $file, array $data): Document
    {
        $tenant = auth()->user()->tenant;
        $fileSize = $file->getSize();

        // 1. Vérification du quota avant upload
        if (!$this->quotaService->canUpload($tenant, $fileSize)) {
            throw new \Exception("Quota d'espace de stockage atteint pour votre entreprise.");
        }

        // 2. Préparation du chemin de stockage (Partitionné par Tenant)
        $path = "tenants/{$tenant->id}/ged";
        if (isset($data['folder_id'])) {
            $path .= "/folder-{$data['folder_id']}";
        }

        // 3. Stockage physique (S3 ou Local selon config)
        $storedPath = $file->store($path, 'public'); // On force 's3' ou on utilise le driver par défaut

        // 4. Enregistrement en base de données
        $document = Document::create([
            'tenant_id' => $tenant->id,
            'user_id'   => auth()->id(),
            'folder_id' => $data['folder_id'] ?? null,
            'name'      => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'size' => $fileSize,
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'description' => $data['description'] ?? null,
            'metadata'  => [
                'original_name' => $file->getClientOriginalName(),
                'uploaded_at'   => now()->toIso8601String(),
            ]
        ]);

        // 5. Mise à jour du quota consommé
        $this->quotaService->incrementUsedStorage($tenant, $fileSize);

        return $document;
    }

    /**
     * Gère la suppression physique et logique
     */
    public function deleteDocument(Document $document): void
    {
        // 1. Suppression du fichier physique
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // 2. Mise à jour du quota (on libère l'espace)
        $this->quotaService->decrementUsedStorage($document->tenant, $document->size);

        // 3. Suppression de la ligne en BDD
        $document->delete();
    }

    /**
     * Génère une réponse de téléchargement sécurisée
     */
    public function downloadDocument(Document $document): StreamedResponse
    {
        return Storage::disk('public')->download(
            $document->file_path,
            $document->name
        );
    }

    /**
     * Récupère l'état du quota pour le frontend
     */
    public function getTenantQuota(Tenants $tenant): array
    {
        return $this->quotaService->getUsageStats($tenant);
    }
}
