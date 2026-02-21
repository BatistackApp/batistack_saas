<?php

namespace App\Http\Controllers\GED;

use App\Enums\GED\DocumentStatus;
use App\Enums\GED\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\GED\BulkActionRequest;
use App\Http\Requests\GED\StoreDocumentRequest;
use App\Http\Requests\GED\StoreFolderRequest;
use App\Http\Requests\GED\UpdateDocumentRequest;
use App\Models\GED\Document;
use App\Models\GED\DocumentFolder;
use App\Services\GED\GEDService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        protected GEDService $gedService
    ) {}

    /**
     * Liste des documents et dossiers (Explorateur)
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenants_id;
        $parentId = $request->query('folder_id');
        $type = $request->query('type');
        $status = $request->query('status');

        // 1. Récupération des dossiers :
        // On ne les affiche que si on n'applique pas de filtre global (type/statut)
        // car les dossiers n'ont pas de type/statut propre.
        $folders = [];
        if (!$type && !$status) {
            $folders = DocumentFolder::where('tenants_id', $tenantId)
                ->where('parent_id', $parentId)
                ->orderBy('name')
                ->get();
        }

        // 2. Récupération des documents avec filtres
        $query = Document::where('tenants_id', $tenantId)
            ->with('uploader:id,first_name,last_name')
            ->latest();

        // Si folder_id est présent, on filtre systématiquement à l'intérieur
        if ($parentId) {
            $query->where('folder_id', $parentId);
        }

        // Filtres optionnels (Type et Statut)
        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $documents = $query->paginate(30);

        // 3. Contexte de navigation (Breadcrumb)
        $currentFolder = $parentId
            ? DocumentFolder::where('tenants_id', $tenantId)->find($parentId)
            : null;

        return response()->json([
            'current_folder' => $currentFolder,
            'breadcrumb' => $this->getBreadcrumb($parentId),
            'folders' => $folders,
            'documents' => $documents,
            'filters' => [
                'type' => $type,
                'status' => $status,
            ]
        ]);
    }

    /**
     * Upload d'un nouveau document.
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        try {
            // Correction : Cast explicite de la string vers l'Enum DocumentType
            $type = DocumentType::from($request->type ?? 'other');

            // Récupération du dossier si fourni
            $folder = $request->folder_id
                ? DocumentFolder::where('tenants_id', auth()->user()->tenants_id)->find($request->folder_id)
                : null;

            $document = $this->gedService->upload(
                $request->file('file'),
                $type,
                null, // documentable (polymorphisme) si non spécifié
                $folder,
                $request->metadata ?? []
            );

            return response()->json($document, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Mise à jour des informations d'un document (Nom, Type, Expiration).
     */
    public function update(UpdateDocumentRequest $request, Document $document): JsonResponse
    {
        $document->update($request->validated());

        return response()->json([
            'message' => 'Document mis à jour',
            'document' => $document,
        ]);
    }

    /**
     * Création d'un dossier
     */
    public function storeFolder(StoreFolderRequest $request): JsonResponse
    {
        $folder = DocumentFolder::create(array_merge(
            $request->validated(),
            ['tenants_id' => auth()->user()->tenants_id]
        ));

        return response()->json($folder, 201);
    }

    /**
     * Téléchargement d'un document
     */
    public function download(Document $document): mixed
    {
        // La policy ou le middleware vérifie déjà le tenant_id
        return $this->gedService->downloadDocument($document);
    }

    /**
     * Récupération des statistiques de stockage pour le tableau de bord.
     */
    public function stats(): JsonResponse
    {
        $stats = $this->gedService->getQuotaStats();

        return response()->json($stats);
    }

    /**
     * Actions groupées
     */
    public function bulk(BulkActionRequest $request): JsonResponse
    {
        $ids = $request->document_ids;
        $action = $request->action;
        $count = 0;

        Document::whereIn('id', $ids)
            ->where('tenants_id', auth()->user()->tenants_id)
            ->chunkById(100, function ($documents) use ($action, $request, &$count) {
                foreach ($documents as $doc) {
                    match ($action) {
                        'delete' => $this->gedService->deleteDocument($doc),
                        'archive' => $doc->update(['status' => DocumentStatus::Archived]),
                        'move' => $doc->update(['folder_id' => $request->target_folder_id]),
                        'validate' => $doc->update(['status' => DocumentStatus::Validated]),
                    };
                    $count++;
                }
            });

        return response()->json(['message' => "$count documents traités avec succès."]);
    }

    /**
     * Suppression simple
     */
    public function destroy(Document $document): JsonResponse
    {
        $this->gedService->deleteDocument($document);

        return response()->json(['message' => 'Document supprimé']);
    }

    /**
     * Génère le fil d'Ariane pour la navigation.
     */
    protected function getBreadcrumb($folderId): array
    {
        if (! $folderId) {
            return [];
        }

        $breadcrumb = [];
        $tenantId = auth()->user()->tenants_id;
        $current = DocumentFolder::where('tenants_id', $tenantId)->find($folderId);

        while ($current) {
            array_unshift($breadcrumb, ['id' => $current->id, 'name' => $current->name]);
            $current = $current->parent_id
                ? DocumentFolder::where('tenants_id', $tenantId)->find($current->parent_id)
                : null;
        }

        return $breadcrumb;
    }
}
