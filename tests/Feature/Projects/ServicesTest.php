<?php

use App\Enums\Projects\ProjectAmendmentStatus;
use App\Enums\Projects\ProjectStatus;
use App\Enums\Projects\ProjectSuspensionReason;
use App\Enums\Projects\ProjectUserRole;
use App\Enums\Tiers\TierStatus;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectAmendment;
use App\Models\Projects\ProjectPhase;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Notifications\Projects\ProjectSuspendedNotification;
use App\Services\Projects\ProjectBudgetService;
use App\Services\Projects\ProjectManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

describe('Service du module: Chantiers', function () {
    it('bloque la transition vers InProgress si le client est suspendu', function () {
        $tier = Tiers::factory()->create(['status' => TierStatus::Suspended]);
        $project = Project::factory()->create(['customer_id' => $tier->id, 'status' => ProjectStatus::Study]);

        $service = new ProjectManagementService;

        expect(fn () => $service->transitionToStatus($project, ProjectStatus::InProgress))
            ->toThrow(Exception::class, 'Le client est suspendu ou non conforme');
    });

    it('calcule un avancement physique pondéré par le budget des phases', function () {
        $project = Project::factory()->create(['initial_budget_ht' => 200000]);

        // Phase A: 100k€, 50% finie
        ProjectPhase::factory()->create([
            'project_id' => $project->id,
            'allocated_budget' => 100000,
            'progress_percentage' => 50,
        ]);

        // Phase B: 10k€, 100% finie
        ProjectPhase::factory()->create([
            'project_id' => $project->id,
            'allocated_budget' => 10000,
            'progress_percentage' => 100,
        ]);

        $service = new ProjectBudgetService;
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
            'status' => ProjectAmendmentStatus::Accepted,
        ]);

        // 3. Ajout d'une phase avec un RAD de 20k
        ProjectPhase::factory()->create([
            'project_id' => $project->id,
            'rad_labor' => 10000,
            'rad_materials' => 10000,
            'allocated_budget' => 50000,
        ]);

        // Simuler un coût réel de 30k (via mock ou injection future)
        // Pour ce test, calculateDetailedCosts renvoie 0, donc Coût Final = 0 + 20k = 20k
        $service = new ProjectBudgetService;
        $summary = $service->getFinancialSummary($project);

        // CA (110k) - CFP (20k) = 90k de marge prévisionnelle
        expect($summary['sales_budget_total'])->toBe(110000.0)
            ->and($summary['total_rad'])->toBe(20000.0)
            ->and($summary['forecast_margin'])->toBe(90000.0);
    });

    it('bloque la suspension d\'un chantier si aucun motif n\'est renseigné', function () {
        $project = Project::factory()->create([
            'status' => ProjectStatus::InProgress,
        ]);

        // Tentative de suspension sans motif
        expect(fn () => $project->update(['status' => ProjectStatus::Suspended]))
            ->toThrow(ValidationException::class);

        $project->refresh();
        expect($project->status)->toBe(ProjectStatus::InProgress);
    });

    it('autorise la suspension d\'un chantier si un motif est renseigné', function () {
        $project = Project::factory()->create([
            'status' => ProjectStatus::InProgress,
        ]);

        $project->update([
            'status' => ProjectStatus::Suspended,
            'suspension_reason' => ProjectSuspensionReason::Weather,
        ]);

        $project->refresh();
        expect($project->status)->toBe(ProjectStatus::Suspended)
            ->and($project->suspension_reason)->toBe(ProjectSuspensionReason::Weather);
    });

    it('envoie une notification aux conducteurs de travaux lors d\'une suspension', function () {
        Notification::fake();

        $project = Project::factory()->create([
            'status' => ProjectStatus::InProgress,
        ]);

        // Affectation d'un conducteur de travaux
        $manager = User::factory()->create();
        $project->members()->attach($manager, ['role' => ProjectUserRole::ProjectManager]);

        $project->update([
            'status' => ProjectStatus::Suspended,
            'suspension_reason' => ProjectSuspensionReason::TechnicalIssue,
        ]);

        Notification::assertSentTo(
            $manager,
            ProjectSuspendedNotification::class,
            function ($notification) use ($project) {
                return $notification->project->id === $project->id;
            }
        );
    });

    it('contient les données correctes dans le tableau de notification (toArray)', function () {
        $project = Project::factory()->create([
            'code_project' => 'CH-2026-TEST',
            'status' => ProjectStatus::Suspended,
            'suspension_reason' => ProjectSuspensionReason::SupplyIssue,
        ]);

        $notification = new ProjectSuspendedNotification($project);
        $data = $notification->toArray(new User);

        expect($data)->toMatchArray([
            'project_id' => $project->id,
            'project_code' => 'CH-2026-TEST',
            'reason' => ProjectSuspensionReason::SupplyIssue->value,
            'reason_label' => ProjectSuspensionReason::SupplyIssue->getLabel(),
            'message' => 'Le chantier CH-2026-TEST est suspendu.',
        ]);
    });

});
