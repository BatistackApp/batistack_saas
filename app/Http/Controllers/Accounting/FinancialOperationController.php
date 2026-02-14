<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
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
    public function closeMonth(int $year, int $month): JsonResponse
    {
        try {
            $closure = $this->closureService->closePeriod($month, $year);
            return response()->json(['message' => "Période {$month}/{$year} clôturée.", 'closure' => $closure]);
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

        ExportFecJob::dispatch($start, $end);

        return response()->json(['message' => 'Génération du FEC lancée. Vous recevrez une notification une fois le fichier prêt.']);
    }
}
