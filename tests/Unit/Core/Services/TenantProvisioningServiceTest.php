<?php

use App\Services\Core\OvhDomainService;
use App\Services\Core\TenantDatabaseService;
use App\Services\Core\TenantProvisioningService;

it('should declare domain in production', function () {
    // Mock de la config
    \Illuminate\Support\Facades\Config::set('app.env', 'production');

    $ovhMock = $this->mock(OvhDomainService::class);
    $ovhMock->shouldReceive('createSubdomain')
        ->once()
        ->with('test-slug', \Mockery::type('int'));

    $dbMock = $this->mock(TenantDatabaseService::class);
    $dbMock->shouldReceive('createSchema')->once();
    $dbMock->shouldReceive('migrateSchema')->once();
    $dbMock->shouldReceive('seedTenantData')->once();

    // Test que shouldDeclareDomain() retourne true en production
    $service = new TenantProvisioningService($ovhMock, $dbMock);
    // Utiliser réflexion pour tester la méthode privée
    $reflection = new \ReflectionMethod($service, 'shouldDeclareDomain');
    $reflection->setAccessible(true);

    expect($reflection->invoke($service))->toBeTrue();
})->skip('Requires refactoring to test private methods properly');
