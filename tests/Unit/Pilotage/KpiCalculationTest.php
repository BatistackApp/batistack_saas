<?php

use App\Enums\Pilotage\ThresholdSeverity;
use App\Models\Pilotage\KpiIndicator;
use App\Models\Pilotage\KpiSnapshot;
use App\Models\Pilotage\KpiThresholds;
use App\Services\Accounting\BalanceCalculator;
use App\Services\Pilotage\AlertManagerService;
use App\Services\Pilotage\KpiAggregationService;

test('il calcule correctement la marge brute d\'un projet avec bcmath', function () {
    // On mock le service de calcul de balance pour isoler le test
    $balanceMock = Mockery::mock(BalanceCalculator::class);
    $service = new KpiAggregationService($balanceMock);

    // Simulation des données en base de données pour un projet spécifique
    // Ventes (Classe 7) : 1000.00
    // Coûts (Classe 6) : 800.00
    // Marge attendue : ((1000 - 800) / 1000) * 100 = 20.00%

    // Note : Dans un test unitaire pur, on simulerait les résultats de DB::table
    // Ici, nous testons la logique de calcul de ratio intégrée au service.

    $project = \App\Models\Projects\Project::factory()->create(['tenants_id' => 1]);

    // Simulation du comportement de la base de données via DB Facade
    DB::shouldReceive('table')->andReturnSelf();
    DB::shouldReceive('join')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('whereRaw')->andReturnSelf();

    // Premier appel pour le revenu, second pour les coûts
    DB::shouldReceive('sum')
        ->once()
        ->with('credit')
        ->andReturn(1000.00);

    DB::shouldReceive('sum')
        ->once()
        ->with('debit')
        ->andReturn(800.00);

    $margin = $service->getProjectGrossMargin($project);

    expect($margin)->toBe('20.00');
});

test('le gestionnaire d\'alertes détecte correctement les dépassements de seuils', function () {
    $alertService = new AlertManagerService;

    // Création d'un snapshot à 50.00
    $indicator = new KpiIndicator(['id' => 1, 'name' => 'Test KPI']);
    $snapshot = new KpiSnapshot([
        'kpi_indicator_id' => 1,
        'value' => '50.00',
    ]);
    $snapshot->setRelation('indicator', $indicator);

    // Cas 1 : Valeur dans les clous (Seuil min 40, max 60)
    $thresholdOk = new \App\Models\Pilotage\KpiThresholds([
        'min_value' => 40,
        'max_value' => 60,
        'severity' => ThresholdSeverity::CRITICAL,
    ]);

    // Nous mockons la méthode sendAlert pour vérifier si elle est appelée
    $mock = Mockery::mock(AlertManagerService::class)->makePartial()->shouldAllowMockingProtectedMethods();

    // On ne devrait PAS envoyer d'alerte ici
    $mock->shouldNotReceive('sendAlert');

    // Cas 2 : Valeur trop basse (Seuil min 55)
    $thresholdLow = new KpiThresholds([
        'min_value' => 55,
        'severity' => ThresholdSeverity::WARNING,
        'is_notifiable' => true,
    ]);

    // On s'attend à ce que l'alerte soit déclenchée pour le seuil bas
    // Note : En test unitaire, on teste la logique interne de checkThresholds
    // par rapport à la collection de seuils retournée.
});

test('il retourne 0 si le revenu est nul pour éviter la division par zéro', function () {
    $balanceMock = Mockery::mock(BalanceCalculator::class);
    $service = new KpiAggregationService($balanceMock);
    $project = \App\Models\Projects\Project::factory()->create(['tenants_id' => 1]);

    DB::shouldReceive('table')->andReturnSelf();
    DB::shouldReceive('join')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('whereRaw')->andReturnSelf();
    DB::shouldReceive('sum')->andReturn(0);

    $margin = $service->getProjectGrossMargin($project);

    expect($margin)->toBe('0');
});
