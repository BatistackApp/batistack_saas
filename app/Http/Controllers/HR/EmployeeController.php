<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreEmployeeRequest;
use App\Http\Requests\HR\UpdateEmployeeRequest;
use App\Models\HR\Employee;
use App\Services\HR\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(protected EmployeeService $employeeService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Employee::query()
            ->with(['manager', 'user'])
            ->when($request->search, function ($q, $search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('external_id', 'like', "%{$search}%");
            })
            ->when($request->department, fn ($q, $dept) => $q->where('department', $dept))
            ->when($request->boolean('active_only'), fn ($q) => $q->where('is_active', true));

        return response()->json($query->latest()->paginate($request->per_page ?? 15));
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->create($request->safe()->except('email', 'tiers_type'), $request->get('email'), $request->get('tiers_type'));

        return response()->json([
            'message' => 'Collaborateur créé avec succès',
            'data' => $employee,
        ], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json($employee->load(['manager', 'skills.skill', 'user']));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee->update($request->validated());

        return response()->json([
            'message' => 'Informations mises à jour',
            'data' => $employee,
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(['message' => 'Collaborateur supprimé (Soft Delete)']);
    }
}
