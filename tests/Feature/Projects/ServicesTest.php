<?php

use App\Enums\Projects\ProjectStatus;
use App\Enums\Tiers\TierStatus;
use App\Models\Projects\Project;
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

    it('remplit la date de début réelle lors du passage en InProgress', function () {
        $tier = Tiers::factory()->create(['status' => TierStatus::Active]);
        $project = Project::factory()->create(['customer_id' => $tier->id, 'status' => ProjectStatus::Study]);

        $service = new ProjectManagementService();
        $service->transitionToStatus($project, ProjectStatus::InProgress);

        expect($project->refresh()->actual_start_at)->not->toBeNull();
    });

    it('calcule correctement la marge brute initiale', function () {
        $project = Project::factory()->create(['initial_budget_ht' => 100000]);

        // On simule des coûts réels à 0 pour l'instant (voir Etape 4)
        $service = new ProjectBudgetService();
        $summary = $service->getFinancialSummary($project);

        expect($summary['margin'])->toBe(100000.0);
    });
});
