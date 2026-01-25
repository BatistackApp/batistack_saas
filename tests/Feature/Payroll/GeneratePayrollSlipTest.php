<?php

use App\Enums\Payroll\PayrollStatus;
use App\Models\Chantiers\Chantier;
use App\Models\Core\Tenant;
use App\Models\HR\Employee;
use App\Models\Payroll\PayrollSlip;
use App\Services\Payroll\GeneratePayrollSlipService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->employee = Employee::factory()->create([
        'tenant_id' => $this->tenant->id,
        'hourly_rate' => 20.00,
        'has_transport_benefit' => true,
    ]);
    $this->chantier = Chantier::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
});

it('generates a payroll slip for an employee', function () {
    // Créer des entrées de pointage
    \App\Models\HR\EmployeeTimesheet::factory(5)->create([
        'employee_id' => $this->employee->id,
        'timesheet_date' => '2025-01-15',
        'total_hours_travel' => 0,
        'total_hours_work' => 8,
        'status' => \App\Enums\HR\TimesheetStatus::Draft
    ]);

    $service = app(GeneratePayrollSlipService::class);

    $slip = $service->generate(
        company: $this->tenant,
        employee: $this->employee,
        year: 2025,
        month: 1,
    );

    expect($slip)->toBeInstanceOf(PayrollSlip::class)
        ->and($slip->status)->toBe(PayrollStatus::Draft)
        ->and($slip->employee_id)->toBe($this->employee->id)
        ->and($slip->total_hours_work)->toBeGreaterThan(0)
        ->and($slip->gross_amount)->toBeGreaterThan(0);
});
