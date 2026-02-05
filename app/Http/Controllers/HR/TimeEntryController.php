<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreTimeEntryRequest;
use App\Http\Requests\HR\VerifyTimeEntryRequest;
use App\Models\HR\TimeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $entries = TimeEntry::with(['employee', 'project'])
            ->where('tenants_id', $request->header('X-Tenant-Id'))
            ->orderBy('date', 'desc')
            ->paginate();

        return response()->json($entries);
    }

    public function store(StoreTimeEntryRequest $request): JsonResponse
    {
        $entry = TimeEntry::create($request->validated());

        return response()->json([
            'message' => 'Temps enregistré avec succès',
            'data' => $entry,
        ], 201);
    }

    public function show(TimeEntry $timeEntry): JsonResponse
    {
        return response()->json($timeEntry->load(['employee', 'project', 'phase']));
    }

    /**
     * Validation/Vérification d'un pointage par un responsable
     */
    public function verify(VerifyTimeEntryRequest $request, TimeEntry $timeEntry): JsonResponse
    {
        $timeEntry->update([
            'status' => $request->status,
            'verified_by' => $request->verified_by,
        ]);

        return response()->json([
            'message' => 'Statut du pointage mis à jour',
            'data' => $timeEntry,
        ]);
    }

    public function destroy(TimeEntry $timeEntry): JsonResponse
    {
        // Sécurité : ne pas supprimer si déjà validé ? (Logique métier)
        if ($timeEntry->status !== \App\Enums\HR\TimeEntryStatus::Draft->value) {
            return response()->json(['message' => 'Impossible de supprimer un pointage validé'], 403);
        }

        $timeEntry->delete();
        return response()->json(['message' => 'Pointage supprimé']);
    }
}
