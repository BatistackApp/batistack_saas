<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreEmployeeSkillRequest;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeSkill;
use App\Models\HR\Skill;
use App\Services\HR\SkillManagerService;
use Illuminate\Http\JsonResponse;

class EmployeeSkillController extends Controller
{
    public function __construct(
        protected SkillManagerService $skillManagerService
    ) {}

    public function index(): JsonResponse
    {
        $skills = EmployeeSkill::with(['employee', 'skill'])
            ->latest()
            ->paginate();

        return response()->json($skills);
    }

    public function store(StoreEmployeeSkillRequest $request): JsonResponse
    {
        $data = $request->validated();
        $employee = Employee::findOrFail($data['employee_id']);
        $skill = Skill::findOrFail($data['skill_id']);

        if ($request->hasFile('document_path')) {
            $data['document_path'] = $request->file('document_path')->store('tenant/'.auth()->user()->tenants_id.'/hr/compliance', 'public');
        }

        $employeeSkill = $this->skillManagerService->assignSkill($employee, $skill, $data);

        return response()->json([
            'message' => 'Habilitation affectée avec succès',
            'data' => $employeeSkill->load(['employee', 'skill']),
        ], 201);
    }

    public function update(StoreEmployeeSkillRequest $request, EmployeeSkill $employeeSkill): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('document_path')) {
            $path = $request->file('document_path')->store('tenant/'.auth()->user()->tenants_id.'/hr/compliance', 'public');
            $data['document_path'] = $path;
        }

        $employeeSkill->update($data);

        return response()->json([
            'message' => 'Affectation mise à jour',
            'data' => $employeeSkill,
        ]);
    }

    public function destroy(EmployeeSkill $employeeSkill): JsonResponse
    {
        $employeeSkill->delete();

        return response()->json(['message' => 'Affectation supprimée']);
    }
}
