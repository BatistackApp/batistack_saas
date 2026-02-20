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

    $this->employee = Employee::factory()->create(['is_active' => true, 'tenants_id' => $this->tenant->id]);

    $this->period = PayrollPeriod::factory()->create([
        'start_date' => '2025-11-01',
        'end_date' => '2025-11-30',
        'status' => PayrollStatus::Draft,
        'tenants_id' => $this->tenant->id,
    ]);

    Queue::fake();
});

test('la génération de paie agrège correctement les heures de pointage approuvées', function () {
    // Création de 2 pointages approuvés (8h chacun = 16h total)
    TimeEntry::factory()->count(2)->create([
        'employee_id' => $this->employee->id,
        'date' => '2025-11-15',
        'hours' => 8,
        'status' => TimeEntryStatus::Approved,
        'has_meal_allowance' => true,
        'tenants_id' => $this->tenant->id,
    ]);

    // Lancement de la génération via l'API
    $response = $this->actingAs($this->admin)
        ->postJson(route('payroll-periods.generate', $this->period));

    $response->assertStatus(200);

    // Vérifier que le bulletin existe
    $payslip = $this->period->payslips()->where('employee_id', $this->employee->id)->first();
    expect($payslip)->not->toBeNull();

    // Vérifier la ligne de salaire de base (16h)
    $baseLine = $payslip->lines()->where('label', 'Salaire de base')->first();
    expect((float) $baseLine->base)->toEqual(16.0);

    // Vérifier les paniers repas (2)
    $mealLine = $payslip->lines()->where('label', 'Indemnité de repas')->first();
    expect((int) $mealLine->base)->toEqual(2);
});

test('la validation d\'une période clôture la paie et lance l\'imputation chantier', function () {
    $response = $this->actingAs($this->admin)
        ->patchJson(route('payroll-periods.validate', $this->period), [
            'status' => PayrollStatus::Validated->value,
            'confirm_lock' => true,
        ]);

    $response->assertStatus(200);
    expect($this->period->refresh()->status)->toBe(PayrollStatus::Validated);

    // Vérifier que le Job d'imputation aux chantiers a été dispatché
    Queue::assertPushed(ProcessPayrollImputationJob::class);
});

test('on ne peut pas modifier un bulletin si la période est validée', function () {
    $this->period->update(['status' => PayrollStatus::Validated]);
    $payslip = \App\Models\Payroll\Payslip::factory()->create(['payroll_period_id' => $this->period->id, 'tenants_id' => $this->tenant->id]);

    $response = $this->actingAs($this->admin)
        ->postJson(route('payslips.adjustments', $payslip), [
            'label' => 'Prime fraude',
            'amount' => 100,
            'type' => 'earning',
            'is_taxable' => true,
        ]);

    // Doit être bloqué par l'authorize() de la FormRequest
    $response->assertStatus(403);
});
