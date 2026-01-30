<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\ProjectUserRequest;
use App\Models\Projects\ProjectUser;

class ProjectUserController extends Controller
{
    public function index()
    {
        return ProjectUser::all();
    }

    public function store(ProjectUserRequest $request)
    {
        return ProjectUser::create($request->validated());
    }

    public function show(ProjectUser $projectUser)
    {
        return $projectUser;
    }

    public function update(ProjectUserRequest $request, ProjectUser $projectUser)
    {
        $projectUser->update($request->validated());

        return $projectUser;
    }

    public function destroy(ProjectUser $projectUser)
    {
        $projectUser->delete();

        return response()->json();
    }
}
