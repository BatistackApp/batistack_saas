<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\ProjectAmendmentRequest;
use App\Models\Projects\ProjectAmendment;

class ProjectAmendmentController extends Controller
{
    public function index()
    {
        return ProjectAmendment::all();
    }

    public function store(ProjectAmendmentRequest $request)
    {
        return ProjectAmendment::create($request->validated());
    }

    public function show(ProjectAmendment $projectAmendment)
    {
        return $projectAmendment;
    }

    public function update(ProjectAmendmentRequest $request, ProjectAmendment $projectAmendment)
    {
        $projectAmendment->update($request->validated());

        return $projectAmendment;
    }

    public function destroy(ProjectAmendment $projectAmendment)
    {
        $projectAmendment->delete();

        return response()->json();
    }
}
