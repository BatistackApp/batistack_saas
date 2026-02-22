<?php

use App\Models\Core\Tenants;
use App\Models\Expense\ExpenseReport;
use App\Services\Expense\ExpenseCalculationService;

beforeEach(function () {
    $this->calcService = new ExpenseCalculationService();
    $this->tenant = Tenants::factory()->create();
});

test('il calcule correctement le HT et la TVA à partir du TTC', function () {
    // Cas standard : 120€ TTC à 20%
    $result = $this->calcService->calculateFromTtc(120, 20);

    expect($result['amount_ht'])->toBe(100.0)
        ->and($result['amount_tva'])->toBe(20.0)
        ->and($result['amount_ttc'])->toBe(120.0);
});

test('il calcule les IK en fonction du barème du tenant', function () {
    // Création d'un barème : 5CV = 0.60€ / km
    \App\Models\Expense\ExpenseMileageScale::create([
        'tenants_id' => $this->tenant->id,
        'vehicle_power' => 5,
        'rate_per_km' => 0.60,
        'active_year' => now()->year,
    ]);

    $amount = $this->calcService->calculateMileage($this->tenant->id, 100, 5);

    // 100km * 0.60 = 60.00€
    expect($amount)->toBe(60.0);
});

test('il utilise un taux par défaut si aucun barème n est trouvé', function () {
    // Aucun barème en base
    $amount = $this->calcService->calculateMileage($this->tenant->id, 100, 7);

    // Fallback à 0.60 par défaut dans le service
    expect($amount)->toBe(60.0);
});
