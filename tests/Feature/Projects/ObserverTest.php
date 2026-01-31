<?php

use App\Enums\Projects\ProjectStatus;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe("Observer du modèle: Project", function () {
    beforeEach(function () {
        $this->tenant = \App\Models\Core\Tenants::factory()->create();
        $this->tier = \App\Models\Tiers\Tiers::factory()->create(['tenants_id' => $this->tenant->id]);
    });

    it('génère automatiquement un code projet à la création', function () {
        $project = Project::create([
            'tenants_id' => 1,
            'customer_id' => $this->tier->id,
            'name' => 'Chantier Test',
            'initial_budget_ht' => 10000,
            'status' => ProjectStatus::Study,
        ]);

        expect($project->code_project)->toStartWith('CH-');
    });
});
