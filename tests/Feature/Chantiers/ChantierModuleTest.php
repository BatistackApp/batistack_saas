<?php

use App\Enums\Chantiers\ChantierStatus;
use App\Enums\Chantiers\CostCategory;
use App\Models\Chantiers\Chantier;
use App\Models\Chantiers\ChantierCost;
use App\Services\Chantiers\ChantierService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('crée un chantier via la factory', function (): void {
    $chantier = Chantier::factory()->create();

    expect($chantier)->toBeInstanceOf(Chantier::class)
        ->and($chantier->status)->toBe(ChantierStatus::Planned)
        ->and($chantier->uuid)->not->toBeEmpty();

    $this->assertDatabaseHas('chantiers', [
        'id' => $chantier->id,
        'name' => $chantier->name,
    ]);
});

it('ajoute un coût et recalcule le total', function (): void {
    $chantier = Chantier::factory()->create(['budget_total' => 0]);

    $service = app(ChantierService::class);

    $service->addCost($chantier, [
        'category' => CostCategory::Materials,
        'label' => 'Ciment',
        'amount' => 1500.50,
        'cost_date' => now(),
    ]);

    $chantier->refresh();

    expect($chantier->costs)->toHaveCount(1)
        ->and($chantier->budget_total)->toBe('1500.50');
});

it('calcule le pourcentage d\'utilisation du budget', function (): void {
    $chantier = Chantier::factory()->create(['budget_total' => 10000]);

    ChantierCost::factory()->create([
        'chantier_id' => $chantier->id,
        'amount' => 5000,
    ]);

    $chantier->refresh();

    expect($chantier->budget_usage_percent)->toBe(50.0);
});

it('génère un code unique pour un nouveau chantier', function (): void {
    $service = app(ChantierService::class);

    $code = $service->generateChantierCode();

    expect($code)->toStartWith('CHT')
        ->and(strlen($code))->toBe(9);
});

it('ferme un chantier correctement', function (): void {
    $chantier = Chantier::factory()->active()->create();

    $service = app(ChantierService::class);
    $service->closeChantier($chantier);

    $chantier->refresh();

    expect($chantier->status)->toBe(ChantierStatus::Completed)
        ->and($chantier->end_date)->not->toBeNull();
});
