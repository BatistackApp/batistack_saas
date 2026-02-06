<?php

use App\Enums\Payroll\PayslipLineType;
use App\Models\Payroll\Payslip;
use App\Services\Payroll\PayrollCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->calcService = new PayrollCalculationService();
});

test('il découpe correctement les heures supplémentaires (151.67h / 25% / 50%)', function () {
    // Cas : 200h de travail total
    // 151.67h base
    // 34.66h à 25% (jusqu'à 186.33h)
    // 13.67h à 50% (le reste)

    $payslip = Payslip::factory()->create();
    $rate = 13.00;

    // Appel d'une méthode protégée via réflexion ou test de l'état final
    $method = new \ReflectionMethod(PayrollCalculationService::class, 'generateHoursLines');
    $method->invoke($this->calcService, $payslip, 200.0, $rate);

    $lines = $payslip->lines()->get();

    // Vérification Salaire de Base
    $baseLine = $lines->where('label', 'Salaire de base')->first();
    expect($baseLine->base)->toEqual(151.67)
        ->and($baseLine->amount_gain)->toEqual(1971.71);

    // Vérification 25%
    $overtime25 = $lines->where('label', 'Heures mensuelles majorées 25%')->first();
    expect($overtime25->base)->toEqual(34.66)
        ->and($overtime25->rate)->toEqual(16.25);

    // Vérification 50% (Nouveau calcul ajouté pour le test)
    $overtime50 = $lines->where('label', 'Heures mensuelles majorées 50%')->first();
    // 200 - 186.33 = 13.67
    expect($overtime50->base)->toEqual(13.67);
});

test('il calcule le net à payer correctement après retenues', function () {
    $payslip = Payslip::factory()->create();

    // Ajout manuel de lignes pour tester la somme
    $payslip->lines()->create([
        'label' => 'Gain',
        'amount_gain' => 2000,
        'type' => PayslipLineType::Earning
    ]);

    $payslip->lines()->create([
        'label' => 'Retenue',
        'amount_deduction' => 400,
        'type' => PayslipLineType::Deduction
    ]);

    $method = new \ReflectionMethod(PayrollCalculationService::class, 'calculateNetToPay');
    $net = $method->invoke($this->calcService, $payslip);

    expect($net)->toBe(1600.0);
});
