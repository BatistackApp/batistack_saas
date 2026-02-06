<?php

namespace App\Http\Controllers\Intervention;

use App\Http\Controllers\Controller;
use App\Http\Requests\Intervention\StoreInterventionTechnicianRequest;
use App\Models\Intervention\Intervention;
use Illuminate\Http\JsonResponse;

class InterventionTechnicianController extends Controller
{
    public function store(StoreInterventionTechnicianRequest $request, Intervention $intervention): JsonResponse
    {
        $intervention->technicians()->syncWithoutDetaching([
            $request->employee_id => ['hours_spent' => $request->hours_spent]
        ]);

        return response()->json(['message' => 'Technicien affecté et heures enregistrées.']);
    }

    public function detach(Intervention $intervention, int $employeeId): JsonResponse
    {
        if ($intervention->status === \App\Enums\Intervention\InterventionStatus::Completed) {
            return response()->json(['error' => 'Intervention déjà clôturée.'], 403);
        }

        $intervention->technicians()->detach($employeeId);
        return response()->json(null, 204);
    }
}
