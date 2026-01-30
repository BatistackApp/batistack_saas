<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\ProjectPhaseRequest;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use Illuminate\Http\JsonResponse;

class ProjectPhaseController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        return response()->json($project->phases);
    }

    public function store(ProjectPhaseRequest $request): JsonResponse
    {
        $phase = ProjectPhase::create($request->validated());

        return response()->json($phase, 201);
    }

    public function update(ProjectPhaseRequest $request, ProjectPhase $projectPhase): JsonResponse
    {
        $projectPhase->update($request->validated());

        return response()->json($projectPhase);
    }

    public function destroy(ProjectPhase $projectPhase): JsonResponse
    {
        $projectPhase->delete();

        return response()->json(null, 204);
    }
}
