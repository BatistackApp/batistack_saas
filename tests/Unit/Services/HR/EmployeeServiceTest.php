<?php

use App\Models\HR\Employee;
use App\Models\HR\EmployeeRate;
use App\Services\HR\EmployeeService;
use Carbon\Carbon;

it('retrieves current rate for an employee', function () {
    $employee = Employee::factory()->create();
    EmployeeRate::factory()->create([
        'employee_id' => $employee->id,
        'hourly_rate' => 15.50,
        'effective_from' => Carbon::now()->subMonth(),
        'effective_to' => null,
    ]);

    $service = new EmployeeService();
    $rate = $service->getCurrentRate($employee);

    expect((float)$rate->hourly_rate)->toBe(15.50);
});

it('sets a new hourly rate for an employee', function () {
    $employee = Employee::factory()->create();
    $service = new EmployeeService();

    $service->setRate($employee, 20.00, Carbon::now());

    $rate = $employee->rates()->latest('effective_from')->first();
    expect((float)$rate->hourly_rate)->toBe(20.00);
});

it('deactivates an employee', function () {
    $employee = Employee::factory()->create(['status' => true]);
    $service = new EmployeeService();

    $service->deactivate($employee);

    expect($employee->refresh()->status)->toBe(0);
});

it('reactivates an employee', function () {
    $employee = Employee::factory()->create(['status' => false, 'resignation_date' => Carbon::now()]);
    $service = new EmployeeService();

    $service->reactivate($employee);

    expect($employee->refresh()->status)->toBe(1)
        ->and($employee->refresh()->resignation_date)->toBeNull();
});
