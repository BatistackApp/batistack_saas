<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\ProjectPhaseRequest;
use App\Models\Projects\ProjectPhase;

class ProjectPhaseController extends Controller
{
    public function index()
    {
        return ProjectPhase::all();
    }

    public function store(ProjectPhaseRequest $request)
    {
        return ProjectPhase::create($request->validated());
    }

    public function show(ProjectPhase $projectPhase)
    {
        return $projectPhase;
    }

    public function update(ProjectPhaseRequest $request, ProjectPhase $projectPhase)
    {
        $projectPhase->update($request->validated());

        return $projectPhase;
    }

    public function destroy(ProjectPhase $projectPhase)
    {
        $projectPhase->delete();

        return response()->json();
    }
}
