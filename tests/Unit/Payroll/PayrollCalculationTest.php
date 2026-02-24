<?php

use App\Enums\Payroll\PayslipLineType;
use App\Models\Payroll\Payslip;
use App\Services\Payroll\PayrollCalculationService;
use App\Services\Payroll\PayrollScaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // 1. On crée un mock du ScaleService pour éviter les erreurs de tables manquantes (payroll_scales, etc.)
    $this->scaleService = Mockery::mock(PayrollScaleService::class);

    // On définit des retours par défaut pour le mock
    $this->scaleService->shouldReceive('getRate')->andReturn(1.20);
    $this->scaleService->shouldReceive('getContributionRates')->andReturn(collect([]));

    $this->calcService = new PayrollCalculationService($this->scaleService);
});

test('le moteur de calcul découpe correctement les tranches d\'heures (151.67h / 25% / 50%)', function () {
    $payslip = Payslip::factory()->create();

    // On prépare l'employé avec les attributs manquants en base
    $employee = $payslip->employee;
    $employee->status = (object) ['value' => 'ouvrier']; // Mock de l'Enum
    $employee->hourly_rate = 13.50;
    $employee->level = 'N3P1';
    $employee->coefficient = '230';
    $employee->btp_travel_zone = 1;

    // CRUCIAL : On force la relation pour que $payslip->employee utilise TOUJOURS notre instance
    $payslip->setRelation('employee', $employee);

    $data = [
        'work' => [
            'total_hours' => 200.0,
            'meal_count' => 0,
            'travel_zones' => collect([]),
        ],
        'absences' => collect([]),
    ];

    $this->calcService->computePayslip($payslip, $data);

    $lines = $payslip->lines()->get();

    // Base : 151.67
    $baseLine = $lines->where('label', 'Salaire de base')->first();
    expect((float) $baseLine->base)->toEqual(151.67);

    // Heures à 25% : 34.66
    $h25 = $lines->where('label', 'Heures mensuelles majorées 25%')->first();
    expect((float) $h25->base)->toEqual(34.66);

    // Heures à 50% : 13.67
    $h50 = $lines->where('label', 'Heures mensuelles majorées 50%')->first();
    expect((float) $h50->base)->toEqual(13.67);
});

test('il calcule correctement le net à payer en sommant les lignes de gains', function () {
    $payslip = Payslip::factory()->create([
        'gross_amount' => 0,
        'net_to_pay' => 0
    ]);

    $payslip->lines()->create([
        'label' => 'Salaire',
        'amount_gain' => 2000,
        'type' => PayslipLineType::Earning,
    ]);

    $payslip->lines()->create([
        'label' => 'Prime',
        'amount_gain' => 500,
        'type' => PayslipLineType::Earning,
    ]);

    $this->calcService->refreshTotals($payslip);

    expect((float) $payslip->refresh()->net_to_pay)->toEqual(2500.0);
});

test('les indemnités de repas sont générées via les données agrégées', function () {
    $payslip = Payslip::factory()->create();

    // Hydratation et verrouillage de la relation
    $employee = $payslip->employee;
    $employee->status = (object) ['value' => 'ouvrier'];
    $employee->hourly_rate = 10.00;
    $payslip->setRelation('employee', $employee);

    $data = [
        'work' => [
            'total_hours' => 151.67,
            'meal_count' => 10,
            'travel_zones' => collect([]),
        ],
        'absences' => collect([]),
    ];

    $this->calcService->computePayslip($payslip, $data);

    $mealLine = $payslip->lines()->where('label', 'Indemnité de repas (Panier)')->first();

    expect($mealLine)->not->toBeNull()
        ->and((float) $mealLine->base)->toEqual(10.0);
});
