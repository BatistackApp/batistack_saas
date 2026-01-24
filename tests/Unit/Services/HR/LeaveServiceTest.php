<?php

use App\Enums\HR\LeaveStatus;
use App\Enums\HR\LeaveType;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeLeave;
use App\Services\HR\LeaveService;
use Carbon\Carbon;

it('creates a leave request', function () {
    $employee = Employee::factory()->create();
    $startDate = Carbon::parse('2025-02-01');
    $endDate = Carbon::parse('2025-02-05');
    $service = new LeaveService();

    $leave = $service->requestLeave($employee, LeaveType::PaidLeave, $startDate, $endDate, 'Vacances');

    expect($leave->employee_id)->toBe($employee->id)
        ->and($leave->status)->toBe(LeaveStatus::Pending);
});

it('approves a leave request', function () {
    $leave = EmployeeLeave::factory()->create(['status' => LeaveStatus::Pending]);
    $service = new LeaveService();

    $service->approve($leave);

    expect($leave->refresh()->status)->toBe(LeaveStatus::Approved);
});

it('rejects a leave request', function () {
    $leave = EmployeeLeave::factory()->create(['status' => LeaveStatus::Pending]);
    $service = new LeaveService();

    $service->reject($leave, 'Budget insuffisant');

    expect($leave->refresh()->status)->toBe(LeaveStatus::Rejected)
        ->and($leave->rejection_reason)->toBe('Budget insuffisant');
});

it('checks if employee is on approved leave for a date', function () {
    $employee = Employee::factory()->create();
    EmployeeLeave::factory()->create([
        'employee_id' => $employee->id,
        'start_date' => Carbon::parse('2025-02-01'),
        'end_date' => Carbon::parse('2025-02-05'),
        'status' => LeaveStatus::Approved,
    ]);

    $service = new LeaveService();
    $isOnLeave = $service->isOnApprovedLeave($employee, Carbon::parse('2025-02-03'));

    expect($isOnLeave)->toBeTrue();
});

it('detects conflicting leaves', function () {
    $employee = Employee::factory()->create();
    EmployeeLeave::factory()->create([
        'employee_id' => $employee->id,
        'start_date' => Carbon::parse('2025-02-05'),
        'end_date' => Carbon::parse('2025-02-15'),
        'status' => LeaveStatus::Approved,
    ]);

    $service = new LeaveService();
    $hasConflict = $service->hasConflictingLeaves($employee, Carbon::parse('2025-02-01'), Carbon::parse('2025-02-10'));

    expect($hasConflict)->toBeTrue();
});

it('retrieves approved leaves for a period', function () {
    $employee = Employee::factory()->create();
    $startDate = Carbon::now()->startOfMonth();
    $endDate = Carbon::now()->endOfMonth();

    EmployeeLeave::factory()->create([
        'employee_id' => $employee->id,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'status' => LeaveStatus::Approved,
    ]);
    EmployeeLeave::factory()->create([
        'employee_id' => $employee->id,
        'status' => LeaveStatus::Pending,
    ]);

    $service = new LeaveService();
    $leaves = $service->getApprovedLeaves($employee, $startDate, $endDate);

    expect($leaves)->toHaveCount(2);
});

it('calculates duration in days correctly', function () {
    $leave = EmployeeLeave::factory()->create([
        'start_date' => Carbon::parse('2025-01-01'),
        'end_date' => Carbon::parse('2025-01-05'),
    ]);

    $service = new LeaveService();
    $duration = $service->calculateDurationInDays($leave);

    expect($duration)->toBe(5);
});
