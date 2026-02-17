<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\PeriodClosureRequest;
use App\Jobs\Accounting\ExportFecJob;
use App\Services\Accounting\PeriodClosureService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialOperationController extends Controller
{
    public function __construct(protected PeriodClosureService $closureService) {}

    /**
     * Déclenche la clôture d'un mois.
     */
    public function closeMonth(PeriodClosureRequest $request): JsonResponse
    {
        try {
            // Utiliser $request->validated('month') et $request->validated('year')
            $closure = $this->closureService->closePeriod($request->validated('month'), $request->validated('year'));

            return response()->json(['message' => "Période {$request->validated('month')}/{$request->validated('year')} clôturée.", 'closure' => $closure]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Lance l'export FEC en arrière-plan.
     */
    public function requestFec(Request $request): JsonResponse
    {
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);

        ExportFecJob::dispatch($start, $end, auth()->user()->id);

        return response()->json(['message' => 'Génération du FEC lancée. Vous recevrez une notification une fois le fichier prêt.']);
    }
}
