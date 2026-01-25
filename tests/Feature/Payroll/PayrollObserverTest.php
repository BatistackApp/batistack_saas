<?php

use App\Enums\Payroll\PayrollStatus;
use App\Models\Core\Tenant;
use App\Models\HR\Employee;
use App\Models\Payroll\PayrollSetting;
use App\Models\Payroll\PayrollSlip;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('auto-validates payroll slip when setting is enabled', function () {
    $tenant = Tenant::factory()->create();
    PayrollSetting::factory()->create([
        'tenant_id' => $tenant->id,
        'auto_validate_payroll' => true,
    ]);

    $employee = Employee::factory()->create(['tenant_id' => $tenant->id]);

    $slip = PayrollSlip::factory()->for($tenant)->for($employee)->create([
        'status' => PayrollStatus::Validated,
    ]);

    expect($slip->validated_at)->toBeNull();
});

it('dispatches accounting job when payroll is validated', function () {
    Queue::fake();

    $slip = PayrollSlip::factory()->create([
        'validated_at' => null,
    ]);

    $slip->update(['validated_at' => now()]);

    Queue::assertPushed(\App\Jobs\Payroll\CreateAccountingEntriesJob::class);
});

it('clears validated_at when critical fields change', function () {
    $slip = PayrollSlip::factory()->create([
        'validated_at' => now(),
        'gross_amount' => 2500,
    ]);

    $slip->update(['gross_amount' => 3000]);

    expect($slip->fresh()->validated_at)->toBeNull();
});
