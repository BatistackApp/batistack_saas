<?php

namespace App\Services\GED;

use App\Exceptions\GED\QuotaExceededException;
use App\Models\Core\Tenants;
use App\Models\GED\Document;
use App\Models\GED\DocumentFolder;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GEDService
{
    public function __construct(
        protected QuotaService $quotaService
    ) {}

    /**
     * Upload un document lié à une ressource spécifique (Projet, Employé, etc.)
     * Respecte la structure : tenants/{tenant_id}/{resource_type}/{resource_id}/file
     */
    public function uploadForResource(UploadedFile $file, Model $resource, array $options = []): Document
    {
        $tenant = auth()->user()->tenant;

        // Vérifier le quota avant upload
        if (! $this->quotaService->canUpload($tenant, $file->getSize())) {
            throw new QuotaExceededException(
                $tenant->storage_used,
                $file->getSize(),
                $this->quotaService->getStorageLimit($tenant)
            );
        }

        $resourceType = Str::plural(strtolower(class_basename($resource)));
        $path = "tenants/{$tenant->id}/{$resourceType}/{$resource->id}";

        return $this->processUpload($file, $tenant, $path, [
            'documentable_type' => get_class($resource),
            'documentable_id' => $resource->id,
            'folder_id' => $options['folder_id'] ?? null,
        ]);
    }

    /**
     * Gère l'upload complet d'un document
     */
    public function uploadDocument(UploadedFile $file, array $data): Document
    {
        $tenant = auth()->user()->tenant;
        $fileSize = $file->getSize();

        // 1. Vérification du quota avant upload
        if (! $this->quotaService->canUpload($tenant, $fileSize)) {
            throw new QuotaExceededException(
                $tenant->storage_used,
                $fileSize,
                $this->quotaService->getStorageLimit($tenant)
            );
        }

        // 2. Préparation du chemin de stockage (Partitionné par Tenant)
        $path = "tenants/{$tenant->id}/ged";
        if (isset($data['folder_id'])) {
            $path .= "/folder-{$data['folder_id']}";
        }

        return $this->processUpload($file, $tenant, $path, $data);
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
     * Supprime le dossier et tous ses documents
     */
    public function deleteFolder(DocumentFolder $folder, Tenants $tenant): void
    {
        // 1. Récupérer tous les documents du dossier
        $documents = $folder->documents()->get();

        // 2. Supprimer chaque document (fichier + DB + quota)
        foreach ($documents as $document) {
            $this->deleteDocument($document);
        }

        // 3. Supprimer le dossier physique S3 (s'il existe)
        $folderPath = "tenants/{$tenant->id}/ged/folder-{$folder->id}";
        if (Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->deleteDirectory($folderPath);
        }

        // 4. Supprimer le dossier de la base de données
        $folder->delete();
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

    /**
     * Récupère les statistiques de quota pour le tenant actuel
     */
    public function getQuotaStats(?Tenants $tenants = null): array
    {
        $tenant = $tenant ?? auth()->user()?->tenant; // Fallback pour le tenant actuel si non fourni
        if (! $tenant) {
            // Gérer l'erreur si aucun tenant ne peut être déterminé
            throw new \RuntimeException('Tenant not found for quota statistics.');
        }

        return $this->quotaService->getUsageStats($tenant);
    }

    /**
     * Logique commune de stockage et création d'entrée en DB
     * La gestion du quota est externalisée à QuotaService
     */
    protected function processUpload(UploadedFile $file, Tenants $tenant, string $path, array $data): Document
    {
        return DB::transaction(function () use ($file, $tenant, $path, $data) {
            $fileName = $file->getClientOriginalName();
            $storagePath = Storage::disk('public')->putFileAs($path, $file, $fileName);

            $document = Document::create(array_merge([
                'name' => $fileName,
                'tenants_id' => $tenant->id,
                'file_name' => $fileName,
                'file_path' => $storagePath,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'user_id' => auth()->id(),
                'extension' => $file->getExtension(),
            ], $data));

            // Mise à jour du quota via QuotaService (UNIQUE SOURCE DE VÉRITÉ)
            $this->quotaService->incrementUsedStorage($tenant, $file->getSize());

            return $document;
        });
    }
}
