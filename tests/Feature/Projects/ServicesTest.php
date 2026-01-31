<?php

use App\Enums\Projects\ProjectAmendmentStatus;
use App\Enums\Projects\ProjectPhaseStatus;
use App\Enums\Projects\ProjectStatus;
use App\Enums\Tiers\TierStatus;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectAmendment;
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

    it('calcule la marge prévisionnelle en incluant les avenants acceptés et le RAD', function () {
        // 1. Création d'un projet avec budget de vente 100k et budget interne décomposé
        $project = Project::factory()->create([
            'initial_budget_ht' => 100000,
            'budget_labor' => 40000,
            'budget_materials' => 30000,
        ]);

        // 2. Ajout d'un avenant accepté de 10k -> CA Total = 110k
        ProjectAmendment::factory()->create([
            'project_id' => $project->id,
            'amount_ht' => 10000,
            'status' => ProjectAmendmentStatus::Accepted
        ]);

        // 3. Ajout d'une phase avec un RAD de 20k
        ProjectPhase::factory()->create([
            'project_id' => $project->id,
            'rad_labor' => 10000,
            'rad_materials' => 10000,
            'allocated_budget' => 50000
        ]);

        // Simuler un coût réel de 30k (via mock ou injection future)
        // Pour ce test, calculateDetailedCosts renvoie 0, donc Coût Final = 0 + 20k = 20k
        $service = new ProjectBudgetService();
        $summary = $service->getFinancialSummary($project);

        // CA (110k) - CFP (20k) = 90k de marge prévisionnelle
        expect($summary['sales_budget_total'])->toBe(110000.0)
            ->and($summary['total_rad'])->toBe(20000.0)
            ->and($summary['forecast_margin'])->toBe(90000.0);
    });

});
