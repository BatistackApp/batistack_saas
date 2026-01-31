<?php

namespace App\Http\Controllers\Projects;

use App\Enums\Projects\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\ProjectRequest;
use App\Models\Projects\Project;
use App\Services\Projects\ProjectBudgetService;
use App\Services\Projects\ProjectManagementService;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectManagementService $managementService,
        protected ProjectBudgetService $budgetService
    ) {}

    public function index(): JsonResponse
    {
        $projects = Project::query()
            ->with(['customer', 'phases'])
            ->latest()
            ->get();

        return response()->json($projects);
    }

    public function store(ProjectRequest $request): JsonResponse
    {
        $project = Project::create($request->validated());

        $this->managementService->transitionToStatus($project, ProjectStatus::Study);

        return response()->json($project, 201);
    }

    public function show(Project $project): JsonResponse
    {
        $project->load(['customer', 'phases.dependency', 'members']); // Consolider le chargement
        $financialSummary = $this->budgetService->getFinancialSummary($project);

        return response()->json([
            'project' => $project,
            'financial_summary' => $financialSummary,
        ]);
    }

    public function update(ProjectRequest $request, Project $project): JsonResponse
    {
        $validated = $request->validated();

        // Si le statut change, on passe par le service pour appliquer les règles métier
        if (isset($validated['status']) && $validated['status'] !== $project->status->value) {
            try {
                $this->managementService->transitionToStatus($project, $validated['status']);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        }

        $project->update($validated);

        return response()->json($project);
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json(null, 204);
    }
}
