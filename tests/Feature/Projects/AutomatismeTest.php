<?php

use App\Jobs\Projects\RecalculateProjectBudgetJob;
use App\Models\Projects\Project;
use App\Models\User;
use App\Notifications\Projects\BudgetThresholdReachedNotification;
use App\Services\Projects\ProjectBudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

describe("TESTS D'AUTOMATISATION: Chantiers", function () {
    it('déclenche une notification si le budget dépasse 90%', function () {
        Notification::fake();

        $project = Project::factory()->create(['initial_budget_ht' => 10000]);
        $ct = User::factory()->create();
        $project->members()->attach($ct, ['role' => 'project_manager']);

        // Mock du service pour simuler une consommation de 95%
        $mockService = mock(ProjectBudgetService::class);
        $mockService->shouldReceive('getFinancialSummary')->andReturn([
            'allocated_budget' => 10000,
            'actual_costs' => 9500,
            'margin' => 500,
        ]);

        $job = new RecalculateProjectBudgetJob($project);
        $job->handle($mockService);

        Notification::assertSentTo($ct, BudgetThresholdReachedNotification::class);
    });
});
