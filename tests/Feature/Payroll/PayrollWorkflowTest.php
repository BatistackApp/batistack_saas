<?php

use App\Enums\HR\TimeEntryStatus;
use App\Enums\Payroll\PayrollStatus;
use App\Jobs\Payroll\ProcessPayrollImputationJob;
use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\HR\TimeEntry;
use App\Models\Payroll\PayrollPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'payroll.manage', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'payroll.validate', 'guard_name' => 'web']);
    $this->tenant = Tenants::factory()->create();
    $this->admin = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->admin->givePermissionTo('payroll.manage', 'payroll.validate');

    $this->employee = Employee::factory()->create([
        'tenants_id' => $this->tenant->id,
        'is_active' => true,
        'monthly_base_salary' => 2275.05, // 15.00 * 151.67
    ]);

    $this->period = PayrollPeriod::factory()->create([
        'tenants_id' => $this->tenant->id,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->endOfMonth(),
        'status' => PayrollStatus::Draft,
    ]);

    // Mock du service de barèmes pour éviter les erreurs de tables manquantes
    $this->scaleService = Mockery::mock(\App\Services\Payroll\PayrollScaleService::class);
    $this->scaleService->shouldReceive('getRate')->andReturn(10.0);
    $this->scaleService->shouldReceive('getContributionRates')->andReturn(collect([]));
    $this->app->instance(\App\Services\Payroll\PayrollScaleService::class, $this->scaleService);

    Queue::fake();
});

test('la génération de paie n\'inclut que les pointages RH "Approved"', function () {
    // Création d'un pointage approuvé (8h)
    TimeEntry::factory()->create([
        'employee_id' => $this->employee->id,
        'tenants_id' => $this->tenant->id,
        'date' => $this->period->start_date->addDay(),
        'hours' => 8,
        'status' => TimeEntryStatus::Approved,
    ]);

    // Création d'un pointage encore en brouillon (4h) - NE DOIT PAS ETRE COMPTE
    TimeEntry::factory()->create([
        'employee_id' => $this->employee->id,
        'tenants_id' => $this->tenant->id,
        'date' => $this->period->start_date->addDays(2),
        'hours' => 4,
        'status' => TimeEntryStatus::Draft,
    ]);

    $this->actingAs($this->admin)
        ->postJson(route('payroll.periods.generate', $this->period))
        ->assertOk();

    $payslip = $this->period->payslips()->where('employee_id', $this->employee->id)->first();

    // On s'assure que le bulletin a été créé
    expect($payslip)->not->toBeNull();

    $baseLine = $payslip->lines()->where('label', 'Salaire de base')->first();

    // On s'assure que la ligne existe
    expect($baseLine)->not->toBeNull();

    // On ne doit avoir que 8h de base
    expect((float) $baseLine->base)->toEqual(8.0);
});

test('on ne peut pas créer deux périodes qui se chevauchent pour le même tenant', function () {
    $this->actingAs($this->admin)
        ->postJson(route('periods.store'), [
            'name' => 'Période Conflit',
            'start_date' => $this->period->start_date->format('Y-m-d'), // Même date
            'end_date' => $this->period->end_date->format('Y-m-d'),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['start_date']);
});

test('la validation d\'une période verrouille les modifications et lance l\'imputation', function () {
    $this->actingAs($this->admin)
        ->postJson(route('payroll.periods.validate', $this->period), [
            'status' => PayrollStatus::Validated->value,
            'confirm_lock' => true,
        ])
        ->assertOk();

    expect($this->period->refresh()->status)->toBe(PayrollStatus::Validated);

    // Le job d'imputation analytique doit être dans la file
    Queue::assertPushed(ProcessPayrollImputationJob::class);
});

test('un utilisateur ne peut pas accéder aux bulletins d\'un autre tenant', function () {
    $otherTenant = Tenants::factory()->create();
    $otherAdmin = User::factory()->create(['tenants_id' => $otherTenant->id]);

    // Création explicite d'un bulletin pour le tenant A pour être sûr qu'il existe
    $payslip = \App\Models\Payroll\Payslip::factory()->create([
        'payroll_period_id' => $this->period->id,
        'employee_id' => $this->employee->id,
        'tenants_id' => $this->tenant->id,
    ]);

    $this->actingAs($otherAdmin)
        ->getJson(route('payroll.payslips.show', $payslip))
        ->assertStatus(404); // Isolation par TenantScope -> 404 Not Found
});

test('on ne peut pas ajouter d\'ajustement à un bulletin clôturé', function () {
    $this->period->update(['status' => PayrollStatus::Validated]);
    $payslip = \App\Models\Payroll\Payslip::factory()->create([
        'payroll_period_id' => $this->period->id,
        'status' => PayrollStatus::Validated,
        'tenants_id' => $this->tenant->id,
    ]);

    $this->actingAs($this->admin)
        ->postJson(route('payroll.payslips.adjustments.store', $payslip), [
            'label' => 'Prime illégale',
            'amount' => 100,
            'type' => 'earning',
            'is_taxable' => true,
        ])
        ->assertStatus(403);
});
