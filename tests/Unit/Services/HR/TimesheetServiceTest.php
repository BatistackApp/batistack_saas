<?php

use App\Enums\HR\TimesheetStatus;
use App\Models\Chantiers\Chantier;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeTimesheet;
use App\Services\HR\TimesheetService;
use Carbon\Carbon;


it('creates or gets a timesheet for an employee', function () {
    $employee = Employee::factory()->create();
    $date = Carbon::now();
    $service = new TimesheetService();

    $timesheet = $service->getOrCreateTimesheet($employee, $date);

    expect($timesheet->employee_id)->toBe($employee->id)
        ->and($timesheet->timesheet_date->toDateString())->toBe($date->toDateString());
});

it('adds a line to a timesheet', closure: function () {
    $tenant = \App\Models\Core\Tenant::factory()->create();
    $employee = Employee::factory()->create();
    $chantier = Chantier::factory()->create(['tenant_id' => $tenant->id]);
    $timesheet = EmployeeTimesheet::factory()->create(['employee_id' => $employee->id]);
    $service = new TimesheetService();

    $line = $service->addLine($timesheet, $chantier->id, 8.00, 1.00);

    expect((float)$line->hours_work)->toBe(8.00)
        ->and((float)$line->hours_travel)->toBe(1.00)
        ->and($timesheet->lines)->toHaveCount(1);
});

it('calculates total hours for a period', function () {
    $employee = Employee::factory()->create();
    $startDate = Carbon::now()->startOfMonth();
    $endDate = Carbon::now()->endOfMonth();

    EmployeeTimesheet::factory()->create([
        'employee_id' => $employee->id,
        'timesheet_date' => $startDate,
        'total_hours_work' => 8.00,
        'total_hours_travel' => 1.00,
    ]);

    $service = new TimesheetService();
    $hours = $service->getTotalHours($employee, $startDate, $endDate);

    expect((float)$hours['total_work'])->toBe(8.00)
        ->and((float)$hours['total_travel'])->toBe(1.00);
});

it('retrieves validated timesheets for a period', function () {
    $employee = Employee::factory()->create();
    $startDate = Carbon::now()->startOfMonth();
    $endDate = Carbon::now()->endOfMonth();

    EmployeeTimesheet::factory()->create([
        'employee_id' => $employee->id,
        'status' => TimesheetStatus::Validated,
    ]);
    EmployeeTimesheet::factory()->create([
        'employee_id' => $employee->id,
        'timesheet_date' => $startDate->copy()->addDay(),
        'status' => TimesheetStatus::Draft,
    ]);

    $service = new TimesheetService();
    $timesheets = $service->getValidatedTimesheets($employee, $startDate, $endDate);

    expect($timesheets)->toHaveCount(1);
});

it('submits a timesheet', function () {
    $timesheet = EmployeeTimesheet::factory()->create(['status' => TimesheetStatus::Draft]);
    $service = new TimesheetService();

    $service->submit($timesheet);

    expect($timesheet->refresh()->status)->toBe(TimesheetStatus::Submitted);
});

it('validates a timesheet', function () {
    $timesheet = EmployeeTimesheet::factory()->create(['status' => TimesheetStatus::Submitted]);
    $service = new TimesheetService();

    $service->validate($timesheet);

    expect($timesheet->refresh()->status)->toBe(TimesheetStatus::Validated);
});
