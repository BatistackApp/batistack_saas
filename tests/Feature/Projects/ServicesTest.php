<?php

use App\Enums\Projects\ProjectPhaseStatus;
use App\Enums\Projects\ProjectStatus;
use App\Enums\Tiers\TierStatus;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\Tiers\Tiers;
use App\Services\Projects\ProjectBudgetService;
use App\Services\Projects\ProjectManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe("Service du module: Chantiers", function () {
    it('bloque la transition vers InProgress si le client est suspendu', function () {
        $tier = Tiers::factory()->create(['status' => TierStatus::Suspended]);
        $project = Project::factory()->create(['customer_id' => $tier->id, 'status' => ProjectStatus::Study]);

        $service = new ProjectManagementService();

        expect(fn() => $service->transitionToStatus($project, ProjectStatus::InProgress))
            ->toThrow(Exception::class, "Le client est suspendu ou non conforme");
    });

    it('calcule un avancement physique pondéré par le budget des phases', function () {
        $project = Project::factory()->create(['initial_budget_ht' => 200000]);

        // Phase A: 100k€, 50% finie
        ProjectPhase::factory()->create([
            'project_id' => $project->id,
            'allocated_budget' => 100000,
            'progress_percentage' => 50
        ]);

        // Phase B: 10k€, 100% finie
        ProjectPhase::factory()->create([
            'project_id' => $project->id,
            'allocated_budget' => 10000,
            'progress_percentage' => 100
        ]);

        $service = new ProjectBudgetService();
        $summary = $service->getFinancialSummary($project);

        // (50k + 10k) / 110k = 54.54%
        expect(round($summary['progress_physical'], 2))->toBe(54.55);
    });

    it('bloque la création d\'une phase si elle dépasse le budget interne global', function () {
        $project = Project::factory()->create(['internal_target_budget_ht' => 50000]);

        $request = new \App\Http\Requests\Projects\ProjectPhaseRequest();
        $request->merge([
            'project_id' => $project->id,
            'allocated_budget' => 60000
        ]);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        expect($validator->fails())->toBeTrue();
    });

});
