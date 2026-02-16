<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\HolidayRequest;
use App\Models\Core\TenantInfoHolidays;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HolidayController extends Controller
{
    public function index(): JsonResponse
    {
        $holidays = TenantInfoHolidays::orderBy('date', 'asc')->get();
        return response()->json($holidays);
    }

    public function store(HolidayRequest $request): JsonResponse
    {
        $holiday = TenantInfoHolidays::create($request->validated());
        return response()->json($holiday, 201);
    }

    /**
     * Déclenchement manuel de la synchronisation automatique (ex: pour l'année prochaine)
     */
    public function sync(Request $request): JsonResponse
    {
        $year = $request->get('year', date('Y'));
        $tenantId = auth()->user()->tenants_id;

        Artisan::call('hr:sync-holidays', [
            'tenant_id' => $tenantId,
            'year' => $year,
        ]);

        return response()->json(['message' => "Calendrier $year synchronisé."]);
    }

    public function destroy(TenantInfoHolidays $holiday): JsonResponse
    {
        $holiday->delete();
        return response()->json(['message' => 'Jour supprimé du calendrier.']);
    }
}
