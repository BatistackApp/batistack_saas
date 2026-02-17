<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreSkillRequest;
use App\Models\HR\Skill;
use Illuminate\Http\JsonResponse;

class SkillController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Skill::all());
    }

    public function store(StoreSkillRequest $request): JsonResponse
    {
        $skill = Skill::create($request->validated());

        return response()->json([
            'message' => 'Type d\'habilitation créé',
            'data' => $skill,
        ], 201);
    }

    public function show(Skill $skill): JsonResponse
    {
        return response()->json($skill);
    }

    public function update(StoreSkillRequest $request, Skill $skill): JsonResponse
    {
        $skill->update($request->validated());

        return response()->json([
            'message' => 'Habilitation mise à jour',
            'data' => $skill,
        ]);
    }

    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();

        return response()->json(['message' => 'Habilitation supprimée']);
    }
}
