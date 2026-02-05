<?php

namespace App\Http\Controllers\GED;

use App\Http\Controllers\Controller;
use App\Http\Requests\GED\BulkActionRequest;
use App\Http\Requests\GED\StoreDocumentRequest;
use App\Http\Requests\GED\StoreFolderRequest;
use App\Models\GED\Document;
use App\Models\GED\DocumentFolder;
use App\Services\GED\GEDService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        protected GEDService $gedService
    ) {}
    /**
     * Liste des documents et dossiers (Explorateur)
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenants_id;
        $parentId = $request->query('folder_id');

        // Récupération des dossiers
        $folders = DocumentFolder::where('tenants_id', $tenantId)
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->get();

        $currentFolder = null;
        if ($parentId) {
            $currentFolder = DocumentFolder::where('tenants_id', $tenantId)->find($parentId);
        }
        // Récupération des documents
        $documents = Document::where('tenants_id', $tenantId)
            ->where('folder_id', $parentId)
            ->with('user:id,first_name,last_name')
            ->latest()
            ->paginate(30);

        return response()->json([
            'current_folder' => $currentFolder,
            'folders' => $folders,
            'documents' => $documents,
            'quota' => $this->gedService->getTenantQuota(auth()->user()->tenant)
        ]);
    }

    /**
     * Upload d'un document
     */
    public function store(StoreDocumentRequest $request)
    {
        try {
            $document = $this->gedService->uploadDocument(
                $request->file('file'),
                $request->validated()
            );

            return response()->json([
                'message' => 'Document uploadé avec succès',
                'document' => $document
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Création d'un dossier
     */
    public function storeFolder(StoreFolderRequest $request)
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
    public function download(Document $document)
    {
        // La policy ou le middleware vérifie déjà le tenant_id
        return $this->gedService->downloadDocument($document);
    }

    /**
     * Actions groupées
     */
    public function bulk(BulkActionRequest $request)
    {
        $ids = $request->document_ids;
        $action = $request->action;

        if ($action === 'delete') {
            Document::whereIn('id', $ids)->each(fn($doc) => $this->gedService->deleteDocument($doc));
            return response()->json(['message' => 'Documents supprimés']);
        }

        if ($action === 'move') {
            Document::whereIn('id', $ids)->update(['folder_id' => $request->target_folder_id]);
            return response()->json(['message' => 'Documents déplacés']);
        }

        return response()->json(['error' => 'Action non reconnue'], 422);
    }

    /**
     * Suppression simple
     */
    public function destroy(Document $document)
    {
        $this->gedService->deleteDocument($document);
        return response()->json(['message' => 'Document supprimé']);
    }
}
