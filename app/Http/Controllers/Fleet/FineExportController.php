<?php

namespace App\Http\Controllers\Fleet;

use App\Enums\Fleet\DesignationStatus;
use App\Http\Controllers\Controller;
use App\Models\Fleet\VehicleFine;
use App\Services\Fleet\AntaiExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FineExportController extends Controller
{
    public function __construct(
        protected AntaiExportService $antaiService
    ) {}

    /**
     * Liste les amendes prêtes à être exportées.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenants_id;

        $fines = $this->antaiService->getPendingFinesForExport($tenantId);

        return response()->json([
            'count' => $fines->count(),
            'fines' => $fines,
        ]);
    }

    /**
     * DÉCLENCHEMENT CONCRET DE L'EXPORT.
     * Cette méthode est appelée quand l'utilisateur clique sur "Générer le CSV ANTAI".
     */
    public function export(Request $request): StreamedResponse|JsonResponse
    {
        $tenantId = auth()->user()->tenants_id;

        // On récupère les amendes à exporter (soit une sélection, soit toutes les "pending")
        $fines = VehicleFine::where('tenants_id', $tenantId)
            ->whereIn('id', $request->input('ids', []))
            ->where('designation_status', DesignationStatus::Pending)
            ->whereNotNull('user_id')
            ->get();

        if ($fines->isEmpty()) {
            return response()->json(['error' => 'Aucune contravention éligible sélectionnée.'], 422);
        }

        // Appel au service pour générer le fichier physiquement
        $filePath = $this->antaiService->generateCsv($fines, $tenantId);

        // On retourne le fichier en téléchargement direct
        return Storage::disk('public')->download($filePath, 'export_antai_'.now()->format('Y-m-d').'.csv');
    }
}
