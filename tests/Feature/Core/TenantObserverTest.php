<?php

use App\Models\Core\Tenants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('logs when tenant is created', function () {
    Log::shouldReceive('info')
        ->with('Tenant created: test-slug', \Mockery::any())
        ->once();

    Tenants::factory()->create(['slug' => 'test-slug']);
});

it('prevents modification of slug after creation', function () {
    $tenant = Tenants::factory()->create(['slug' => 'original-slug']);

    expect(fn () => $tenant->update(['slug' => 'new-slug']))
        ->toThrow(\Exception::class);
});

it('logs when tenant is soft-deleted', function () {
    Log::shouldReceive('info')
        ->with('Tenant created: test-slug', \Mockery::any())
        ->once();

    Log::shouldReceive('warning')
        ->with('Tenant soft-deleted: test-slug', \Mockery::any())
        ->once();

    $tenant = Tenants::factory()->create(['slug' => 'test-slug']);
    $tenant->delete();
});
