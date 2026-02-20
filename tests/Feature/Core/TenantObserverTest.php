<?php

use App\Models\Core\Tenants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);


it('prevents modification of slug after creation', function () {
    $tenant = Tenants::factory()->create(['slug' => 'original-slug']);

    expect(fn () => $tenant->update(['slug' => 'new-slug']))
        ->toThrow(\Exception::class);
});

