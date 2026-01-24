<?php

use App\Enums\HR\TimesheetStatus;
use App\Models\Chantiers\Chantier;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeRate;
use App\Models\HR\EmployeeTimesheet;
use App\Models\HR\EmployeeTimesheetLine;
use App\Services\HR\ChantiersLaborCostService;
use Carbon\Carbon;

it('calculates labor cost for a chantier', function () {
    $tenant = \App\Models\Core\Tenant::factory()->create();
    $chantier = Chantier::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->create(['tenant_id' => $tenant->id]);
    $rate = EmployeeRate::factory()->create([
        'employee_id' => $employee->id,
        'hourly_rate' => 20.00,
        'effective_from' => now()->subDay(),
    ]);

    $timesheet = EmployeeTimesheet::factory()->create([
        'employee_id' => $employee->id,
        'status' => TimesheetStatus::Validated,
        'timesheet_date' => now(),
        'total_hours_work' => 10.00,
        'total_hours_travel' => 0.00,
    ]);

    EmployeeTimesheetLine::factory()->create([
        'employee_timesheet_id' => $timesheet->id,
        'chantier_id' => $chantier->id,
        'hours_work' => 10.00,
    ]);

    $employeeServiceMock = mock(App\Services\HR\EmployeeService::class);
    $employeeServiceMock->shouldReceive('getCurrentRate')->andReturn($rate);

    // Injection du mock via le service container ou directement
    $service = new ChantiersLaborCostService($employeeServiceMock);
    $cost = $service->calculateChantieLaborCost($chantier, now(), now());

    expect($cost['total_cost'])->toBe(200.00);
});

it('retrieves total hours for a chantier', function () {
    $tenant = \App\Models\Core\Tenant::factory()->create();
    $chantier = Chantier::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->create(['tenant_id' => $tenant->id]);

    $timesheet = EmployeeTimesheet::factory()->create(['employee_id' => $employee->id, 'status' => TimesheetStatus::Validated]);
    EmployeeTimesheetLine::factory()->create([
        'employee_timesheet_id' => $timesheet->id,
        'chantier_id' => $chantier->id,
        'hours_work' => 8.00,
        'hours_travel' => 1.00,
    ]);

    $emSer = new \App\Services\HR\EmployeeService();
    $service = new ChantiersLaborCostService($emSer);
    $hours = $service->getChantieTotalHours($chantier, now()->subYear(), now());

    expect($hours['total_hours_work'])->toBe(8.00)
        ->and($hours['total_hours_travel'])->toBe(1.00);
});

it('generates labor cost report for all chantiers', function () {
    $tenant = \App\Models\Core\Tenant::factory()->create();
    $startDate = Carbon::now()->startOfMonth();
    $endDate = Carbon::now()->endOfMonth();

    Chantier::factory()->count(2)->create(['tenant_id' => $tenant->id]);

    $emSer = new \App\Services\HR\EmployeeService();
    $service = new ChantiersLaborCostService($emSer);
    $report = $service->getChantiersCostsReport($startDate, $endDate);

    expect($report)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});
