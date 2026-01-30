<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\ProjectRequest;
use App\Models\Projects\Project;

class ProjectController extends Controller
{
    public function index()
    {
        return Project::all();
    }

    public function store(ProjectRequest $request)
    {
        return Project::create($request->validated());
    }

    public function show(Project $project)
    {
        return $project;
    }

    public function update(ProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return $project;
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json();
    }
}
