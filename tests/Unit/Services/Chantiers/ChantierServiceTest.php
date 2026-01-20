<?php

use App\Models\Chantiers\Chantier;
use App\Services\Chantiers\ChantierService;

it('crée un chantier avec un UUID automatique', function (): void {
    $service = app(ChantierService::class);

    $chantier = $service->createChantier([
        'name' => 'Projet Test',
        'budget_total' => 50000,
    ]);

    expect($chantier->uuid)->not->toBeEmpty()
        ->and($chantier->code)->toStartWith('CHT');
});

it('génère un code séquentiel', function (): void {
    Chantier::factory()->create(['code' => 'CHT000001']);

    $service = app(ChantierService::class);
    $newCode = $service->generateChantierCode();

    expect($newCode)->toBe('CHT000002');
});
