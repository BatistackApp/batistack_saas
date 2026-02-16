<?php

use App\Enums\Accounting\AccountType;
use App\Enums\Accounting\JournalType;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Journal;
use App\Models\Accounting\PeriodClosure;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\User;
use App\Services\Accounting\AccountingEntryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Nettoyage des permissions pour éviter les conflits de cache entre tests
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Configuration du Tenant et de l'utilisateur
    if (!\Spatie\Permission\Models\Permission::where('name', 'accounting.manage')->exists()) {
        \Spatie\Permission\Models\Permission::create(['name' => 'accounting.manage', 'guard_name' => 'web']);
    }

    $this->tenant = \App\Models\Core\Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->user->givePermissionTo('accounting.manage');

    // On authentifie l'utilisateur immédiatement pour activer les GlobalScopes (HasTenant)
    $this->actingAs($this->user);
    $this->tenantId = $this->tenant->id;

    // Création d'un journal d'achats via factory
    $this->journal = Journal::factory()->create([
        'tenants_id' => $this->tenantId,
        'code' => 'AC',
        'label' => 'Achats',
        'type' => JournalType::PurchasesJournal,
        'is_active' => true,
    ]);

    // Création de comptes de base via factory
    $this->account401 = ChartOfAccount::factory()->create([
        'tenants_id' => $this->tenantId,
        'account_number' => '401000',
        'account_label' => 'Fournisseurs',
        'nature' => AccountType::Liability,
    ]);

    $this->account601 = ChartOfAccount::factory()->create([
        'tenants_id' => $this->tenantId,
        'account_number' => '601000',
        'account_label' => 'Achats de matières',
        'nature' => AccountType::Expense,
    ]);

    Notification::fake();
    Queue::fake();
});

test('une écriture doit être équilibrée pour être validée', function () {
    $service = app(AccountingEntryService::class);

    // Forcer Carbon\Carbon pour éviter le conflit avec CarbonImmutable de Laravel 11
    $date = Carbon::instance(now());

    // Création d'une écriture déséquilibrée
    expect(fn() => $service->create(
        $this->journal,
        $date,
        'Achat Ciment',
        [
            ['chart_of_account_id' => $this->account601->id, 'debit' => 100.00, 'credit' => 0],
            ['chart_of_account_id' => $this->account401->id, 'debit' => 0, 'credit' => 99.99], // Erreur de 0.01
        ]
    ))->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('une écriture validée devient immuable', function () {
    $service = app(AccountingEntryService::class);
    $date = Carbon::instance(now());

    $entry = $service->create(
        $this->journal,
        $date,
        'Achat Outillage',
        [
            ['chart_of_account_id' => $this->account601->id, 'debit' => 50.00, 'credit' => 0],
            ['chart_of_account_id' => $this->account401->id, 'debit' => 0, 'credit' => 50.00],
        ]
    );

    $service->validate($entry);

    // Tentative de modification du libellé (via l'Observer)
    expect(fn() => $entry->update(['label' => 'Fraude']))
        ->toThrow(\RuntimeException::class, "Une écriture validée ne peut plus être modifiée.");
});

test('on ne peut pas créer d\'écriture dans une période clôturée', function () {
    // Clôture du mois précédent
    $lastMonth = Carbon::instance(now())->subMonth();

    // Utilisation de create() direct pour éviter les erreurs de typage factory/grammar
    PeriodClosure::create([
        'tenants_id' => $this->tenantId,
        'month' => $lastMonth->month,
        'year' => $lastMonth->year,
        'period_start' => $lastMonth->copy()->startOfMonth(),
        'period_end' => $lastMonth->copy()->endOfMonth(),
        'is_locked' => true,
        'closed_at' => now(),
        'closed_by' => $this->user->id,
    ]);

    $service = app(AccountingEntryService::class);

    // Tentative de création dans la période verrouillée
    expect(fn() => $service->create(
        $this->journal,
        $lastMonth,
        'Ecriture tardive',
        [
            ['chart_of_account_id' => $this->account601->id, 'debit' => 10, 'credit' => 0],
            ['chart_of_account_id' => $this->account401->id, 'debit' => 0, 'credit' => 10],
        ]
    ))->toThrow(\RuntimeException::class);
});

test('l\'imputation analytique BTP est correctement enregistrée sur les lignes', function () {
    $project = Project::factory()->create(['tenants_id' => $this->tenantId]);
    $phase = ProjectPhase::factory()->create(['project_id' => $project->id]);

    $service = app(AccountingEntryService::class);
    $date = Carbon::instance(now());

    $entry = $service->create(
        $this->journal,
        $date,
        'Location Pelle - Chantier A',
        [
            [
                'chart_of_account_id' => $this->account601->id,
                'debit' => 500.00,
                'credit' => 0,
                'project_id' => $project->id,
                'project_phase_id' => $phase->id
            ],
            [
                'chart_of_account_id' => $this->account401->id,
                'debit' => 0,
                'credit' => 500.00
            ],
        ]
    );

    $lineWithProject = $entry->lines()->where('debit', '>', 0)->first();

    expect($lineWithProject->project_id)->toBe($project->id)
        ->and($lineWithProject->project_phase_id)->toBe($phase->id);
});

test('le solde d\'un compte est calculé avec précision via le service', function () {
    $service = app(AccountingEntryService::class);
    $calc = app(\App\Services\Accounting\BalanceCalculator::class);
    $date = Carbon::today();

    // Plusieurs écritures sur le compte 601
    $entries = [
        ['d' => '150.55', 'c' => '0'],
        ['d' => '49.45', 'c' => '0'],
        ['d' => '0', 'c' => '50.00'], // Un avoir/remboursement
    ];

    foreach ($entries as $data) {
        $entry = $service->create($this->journal, $date, 'Test Solde', [
            ['chart_of_account_id' => $this->account601->id, 'debit' => $data['d'], 'credit' => $data['c']],
            ['chart_of_account_id' => $this->account401->id, 'debit' => $data['c'], 'credit' => $data['d']],
        ]);
        $service->validate($entry);
    }

    // Solde attendu : 150.55 + 49.45 - 50.00 = 150.00
    $balance = $calc->calculate($this->account601);

    expect(bccomp($balance, '150.0000', 4))->toBe(0);
});

test('respect strict de l\'isolation multi-tenant', function () {
    // On s'assure d'être déconnecté pour créer les données de l'autre tenant
    auth()->logout();

    $otherTenant = \App\Models\Core\Tenants::factory()->create();

    $otherJournal = Journal::factory()->create([
        'tenants_id' => $otherTenant->id,
        'code' => 'BQ2',
        'label' => 'Banque concurrent',
        'type' => JournalType::BankJournal,
    ]);

    // On se reconnecte avec l'utilisateur du premier tenant
    $this->actingAs($this->user);

    // On force la mise à jour de l'utilisateur authentifié pour le scope HasTenant
    auth()->setUser($this->user);

    // Le journal du second tenant ne doit pas être visible via Eloquent
    $exists = Journal::where('id', $otherJournal->id)->exists();

    expect($exists)->toBeFalse();
});
