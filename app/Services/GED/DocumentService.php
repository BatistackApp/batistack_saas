<?php

namespace App\Services\GED;

use App\Models\Core\Tenants;
use App\Models\GED\Document;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    public function __construct(
        protected QuotaService $quotaService
    ) {}

    /**
     * Upload un document et le lie à une entité (Projet, Employé, etc.)
     */
    public function store(
        UploadedFile $file,
        Tenants $tenant,
        User $user,
        ?Model $documentable = null,
        array $data = []
    ): Document {
        $fileSize = $file->getSize();

        // 1. Vérification du quota avant toute action
        if (!$this->quotaService->canUpload($tenant, $fileSize)) {
            throw new Exception("Quota de stockage atteint (1Go). Veuillez souscrire à une extension d'espace.");
        }

        // 2. Génération du chemin de stockage sécurisé
        // Format: tenants/{uuid}/documents/{year}/{month}/{random_name}.ext
        $path = "tenants/{$tenant->id}/documents/" . date('Y/m');
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $storedPath = $file->storeAs($path, $fileName, 'private');

        // 3. Création de l'enregistrement en base de données
        return Document::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'documentable_type' => $documentable ? get_class($documentable) : null,
            'documentable_id' => $documentable?->getKey(),
            'folder_id' => $data['folder_id'] ?? null,
            'name' => $data['name'] ?? $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'file_name' => $fileName,
            'mime_type' => $file->getMimeType(),
            'size' => $fileSize,
            'extension' => $file->getClientOriginalExtension(),
            'metadata' => $data['metadata'] ?? [],
            'expires_at' => $data['expires_at'] ?? null,
            'version' => $data['version'] ?? 1,
        ]);
    }

    /**
     * Supprime physiquement et logiquement un document.
     */
    public function delete(Document $document): bool
    {
        // On supprime le fichier physique
        if (Storage::disk('private')->exists($document->file_path)) {
            Storage::disk('private')->delete($document->file_path);
        }

        // Suppression de la base de données
        return $document->forceDelete();
    }

    /**
     * Génère une URL temporaire sécurisée pour la consultation (si S3 ou stockage protégé).
     */
    public function getTemporaryUrl(Document $document, int $minutes = 60): string
    {
        return Storage::disk('private')->temporaryUrl(
            $document->file_path,
            now()->addMinutes($minutes)
        );
    }
}
