<?php

use App\Enums\HR\AbsenceRequestStatus;
use App\Enums\HR\AbsenceType;
use App\Enums\HR\TimeEntryStatus;
use App\Enums\Payroll\PayrollStatus;
use App\Models\Core\Tenants;
use App\Models\HR\AbsenceRequest;
use App\Models\HR\Employee;
use App\Models\HR\TimeEntry;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\Payslip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Configuration des permissions pour le test
    Permission::firstOrCreate(['name' => 'payroll.manage', 'guard_name' => 'web']);

    // Création du contexte Tenant et Admin
    $this->tenant = Tenants::factory()->create();
    $this->admin = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->admin->givePermissionTo('payroll.manage');

    // Employé configuré avec des données BTP
    $this->employee = Employee::factory()->create([
        'tenants_id' => $this->tenant->id,
        'monthly_base_salary' => 2275.05,
        'btp_level' => 'ouvrier',
        'is_active' => true,
    ]);

    // Période de paie par défaut
    $this->period = PayrollPeriod::factory()->create([
        'tenants_id' => $this->tenant->id,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->endOfMonth(),
        'status' => PayrollStatus::Draft,
    ]);
});

test('il applique les déductions pour absences approuvées sur le bulletin', function () {
    // 1. Absence de 2 jours (14h)
    AbsenceRequest::factory()->create([
        'employee_id' => $this->employee->id,
        'tenants_id' => $this->tenant->id,
        'starts_at' => $this->period->start_date->copy()->addDays(5),
        'ends_at' => $this->period->start_date->copy()->addDays(6),
        'duration_days' => 2.0,
        'type' => AbsenceType::UnpaidLeave,
        'status' => AbsenceRequestStatus::Approved,
    ]);

    // 2. Pointages
    TimeEntry::factory()->create([
        'employee_id' => $this->employee->id,
        'tenants_id' => $this->tenant->id,
        'date' => $this->period->start_date->format('Y-m-d'),
        'hours' => 140,
        'status' => TimeEntryStatus::Approved,
    ]);

    $this->actingAs($this->admin)
        ->postJson(route('payroll.periods.generate', $this->period));

    $payslip = Payslip::where('employee_id', $this->employee->id)->first();

    // Vérification de la retenue (14h * 15€ = 210€)
    $this->assertDatabaseHas('payslip_lines', [
        'payslip_id' => $payslip->id,
        'amount_deduction' => 210.00,
        'type' => 'deduction',
    ]);
});

test('il refuse la suppression d\'une période déjà validée', function () {
    $this->period->update(['status' => PayrollStatus::Validated]);

    $this->actingAs($this->admin)
        ->deleteJson(route('payroll.periods.destroy', $this->period))
        ->assertStatus(422);
});

test('il déclenche l\'export comptable avec succès', function () {
    Queue::fake();

    $this->actingAs($this->admin)
        ->postJson(route('payroll.periods.export', $this->period), [
            'format' => 'sage',
            'recipient_email' => 'comptable@batistack.fr',
        ])
        ->assertStatus(200);
});
