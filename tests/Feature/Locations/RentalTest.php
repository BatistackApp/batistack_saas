<?php

use App\Models\Locations\RentalContract;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Services\Locations\RentalCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'locations.manage', 'guard_name' => 'web']);
    $this->tenant = \App\Models\Core\Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->user->givePermissionTo(['locations.manage']);
    $this->tenantsId = $this->tenant->id;

    // Création d'un loueur conforme
    $this->provider = Tiers::factory()->create([
        'tenants_id' => $this->tenantsId,
        'status' => \App\Enums\Tiers\TierStatus::Active,
    ]);

    $this->project = Project::factory()->create(['tenants_id' => $this->tenantsId]);
    $this->phase = ProjectPhase::factory()->create(['project_id' => $this->project->id]);

    $this->calcService = new RentalCalculationService;
});

it('Affichage de la liste des locations', function () {
    $locations = RentalContract::factory(15)->create(['tenants_id' => $this->tenantsId]);

    $response = $this->actingAs($this->user)
        ->get(route('rental-contracts.index'));

    $response->assertStatus(200);
});

it("Création d'un contrat de location", function () {
    $payload = [
        'provider_id' => $this->provider->id,
        'project_id' => $this->project->id,
        'label' => 'Test de Location',
        'start_date_planned' => Carbon::now()->toDateString(),
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('rental-contracts.store'), $payload);

    $response->assertStatus(201);
});
