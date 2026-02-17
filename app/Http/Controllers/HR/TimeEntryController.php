<?php

namespace App\Http\Controllers\HR;

use App\Enums\HR\TimeEntryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreTimeEntryRequest;
use App\Http\Requests\HR\VerifyTimeEntryRequest;
use App\Models\HR\Employee;
use App\Models\HR\TimeEntry;
use App\Services\HR\TimeTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function __construct(
        protected TimeTrackingService $timeTrackingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $entries = TimeEntry::with(['employee', 'project', 'phase'])
            ->orderBy('date', 'desc')
            ->paginate();

        return response()->json($entries);
    }

    /**
     * Liste des pointages pour un employé spécifique
     * Utile pour le dashboard de l'ouvrier ou le suivi individuel
     */
    public function indexByEmployee(Request $request, Employee $employee): JsonResponse
    {
        $entries = $employee->timeEntries()
            ->with(['project', 'phase'])
            ->when($request->start_date, fn ($q) => $q->where('date', '>=', $request->start_date))
            ->when($request->end_date, fn ($q) => $q->where('date', '<=', $request->end_date))
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'employee' => $employee->only(['id', 'first_name', 'last_name']),
            'entries' => $entries,
            'summary' => [
                'total_hours' => $entries->sum('hours'),
                'total_travel_time' => $entries->sum('travel_time'),
            ],
        ]);
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
        $targetStatus = TimeEntryStatus::from($request->status);

        try {
            switch ($targetStatus) {
                case TimeEntryStatus::Verified:
                    $this->timeTrackingService->verifyEntry($timeEntry);
                    $message = 'Pointage vérifié avec succès par le niveau N1.';
                    break;

                case TimeEntryStatus::Approved:
                    $this->timeTrackingService->approveEntry($timeEntry);
                    $message = 'Pointage approuvé définitivement et imputé au projet.';
                    break;

                case TimeEntryStatus::Rejected:
                    $this->timeTrackingService->rejectEntry($timeEntry, $request->notes);
                    $message = "Le pointage a été rejeté et renvoyé à l'employé.";
                    break;

                default:
                    return response()->json(['error' => 'Transition de statut non gérée.'], 422);
            }

            return response()->json([
                'message' => $message,
                'data' => $timeEntry->fresh(['employee', 'project']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(TimeEntry $timeEntry): JsonResponse
    {
        if ($timeEntry->status === TimeEntryStatus::Approved) {
            return response()->json(['error' => 'Impossible de supprimer un pointage déjà approuvé.'], 422);
        }

        $timeEntry->delete();

        return response()->json(['message' => 'Pointage supprimé']);
    }
}
