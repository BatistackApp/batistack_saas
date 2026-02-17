<?php

use App\Enums\Pilotage\KpiCategory;
use App\Enums\Pilotage\KpiUnit;
use App\Models\Core\Tenants;
use App\Models\Pilotage\KpiIndicator;
use App\Models\Pilotage\KpiSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Création de la permission nécessaire
    Permission::firstOrCreate(['name' => 'pilotage.manage', 'guard_name' => 'web']);

    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create([
        'tenants_id' => $this->tenant->id,
    ]);

    $this->user->givePermissionTo('pilotage.manage');
});

test('un gestionnaire peut configurer un nouvel indicateur KPI via l\'API', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('kpi.indicators.store'), [
            'name' => 'Marge Brute Globale',
            'code' => 'global_margin',
            'category' => KpiCategory::FINANCIAL->value,
            'unit' => KpiUnit::PERCENTAGE->value,
            'is_active' => true,
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('code', 'global_margin');

    $this->assertDatabaseHas('kpi_indicators', [
        'tenants_id' => $this->tenant->id,
        'code' => 'global_margin',
    ]);
});

test('un utilisateur ne peut pas voir les indicateurs d\'un autre tenant', function () {
    // Création d'un indicateur pour un autre tenant
    $otherTenant = Tenants::factory()->create();

    // On force la création hors scope si nécessaire
    KpiIndicator::withoutEvents(function () use ($otherTenant) {
        KpiIndicator::create([
            'tenants_id' => $otherTenant->id,
            'name' => 'Secret KPI',
            'code' => 'secret_kpi',
            'category' => KpiCategory::EQUIPMENT,
            'unit' => KpiUnit::COUNT,
            'is_active' => true,
        ]);
    });

    $response = $this->actingAs($this->user)
        ->getJson(route('kpi.indicators.index'));

    $response->assertStatus(200);

    // On vérifie que le code n'est pas présent dans la collection retournée
    $codes = collect($response->json())->pluck('code');
    expect($codes)->not->toContain('secret_kpi');
});

test('le déclenchement manuel d\'un snapshot génère des données historisées', function () {
    // On utilise un code reconnu par le SnapshotOrchestrator (net_cash)
    $indicator = KpiIndicator::factory()->create([
        'tenants_id' => $this->tenant->id,
        'code' => 'net_cash',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('kpi.snapshots.trigger'), [
            'kpi_indicator_id' => $indicator->id,
        ]);

    $response->assertStatus(200);

    // On vérifie l'existence dans la DB en ignorant les scopes si besoin pour le test
    $this->assertDatabaseHas('kpi_snapshots', [
        'tenants_id' => $this->tenant->id,
        'kpi_indicator_id' => $indicator->id,
    ]);
});

test('l\'historique d\'un indicateur retourne une série chronologique de valeurs', function () {
    $indicator = KpiIndicator::factory()->create([
        'tenants_id' => $this->tenant->id,
        'is_active' => true,
    ]);

    // Création manuelle des snapshots pour ce tenant
    for ($i = 5; $i >= 1; $i--) {
        KpiSnapshot::create([
            'tenants_id' => $this->tenant->id,
            'kpi_indicator_id' => $indicator->id,
            'value' => (string) (100 + $i),
            'measured_at' => now()->subDays($i),
        ]);
    }

    $response = $this->actingAs($this->user)
        ->getJson(route('kpi.indicators.history', $indicator));

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
});

test('un utilisateur sans permission ne peut pas déclencher de snapshot', function () {
    $simpleUser = User::factory()->create(['tenants_id' => $this->tenant->id]);

    // On ne donne PAS la permission manage-kpi

    $response = $this->actingAs($simpleUser)
        ->postJson(route('kpi.snapshots.trigger'));

    $response->assertStatus(403);
});
