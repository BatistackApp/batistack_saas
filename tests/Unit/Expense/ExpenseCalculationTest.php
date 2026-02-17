<?php

use App\Services\Expense\ExpenseCalculationService;

beforeEach(function () {
    $this->calcService = new ExpenseCalculationService;
});

test('il calcule correctement le HT et la TVA à partir du TTC', function () {
    // Cas standard : 120€ TTC à 20%
    $result = $this->calcService->calculateFromTtc(120, 20);

    expect($result['amount_ht'])->toBe(100.0)
        ->and($result['amount_tva'])->toBe(20.0)
        ->and($result['amount_ttc'])->toBe(120.0);
});

test('il gère les montants avec des décimales complexes', function () {
    // Cas : 45.55€ TTC à 5.5%
    $result = $this->calcService->calculateFromTtc(45.55, 5.5);

    // HT = 45.55 / 1.055 = 43.1753... arrondi à 43.18
    // TVA = 45.55 - 43.18 = 2.37
    expect($result['amount_ht'])->toBe(43.18)
        ->and($result['amount_tva'])->toBe(2.37);
});
