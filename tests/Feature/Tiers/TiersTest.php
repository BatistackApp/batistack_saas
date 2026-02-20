<?php

use App\Enums\Tiers\TierComplianceStatus;
use App\Enums\Tiers\TierDocumentStatus;
use App\Enums\Tiers\TierDocumentType;
use App\Enums\Tiers\TierStatus;
use App\Enums\Tiers\TierType as TierTypeEnum;
use App\Jobs\Tiers\CheckTiersActivityJob;
use App\Models\Tiers\TierDocument;
use App\Models\Tiers\TierDocumentRequirement;
use App\Models\Tiers\TierQualification;
use App\Models\Tiers\Tiers;
use App\Services\SirenService;
use App\Services\Tiers\TierCodeGenerator;
use App\Services\Tiers\TierSearchService;
use App\Services\Tiers\TierTypeManager;
use App\Services\Tiers\TierValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Tier Model', function () {
    it('creates a tier with valid data', function () {
        $tier = Tiers::factory()->create([
            'type_entite' => 'personne_morale',
            'raison_social' => 'Test Company',
            'status' => TierStatus::Active,
        ]);

        expect($tier)->toBeInstanceOf(Tiers::class)
            ->and($tier->raison_social)->toBe('Test Company')
            ->and($tier->status)->toBe(TierStatus::Active);
    });

    it('la retenue de garantie est de 5% par défaut', function () {
        $tier = Tiers::factory()->create(['retenue_garantie_pct' => 5.00]);
        expect($tier->retenue_garantie_pct)->toBe('5.00');
    });

    it('un tiers devient non-conforme si un document expire', function () {
        $tier = Tiers::factory()->create();
        TierDocument::create([
            'tiers_id' => $tier->id,
            'type' => 'URSSAF',
            'expires_at' => now()->subDay(),
            'status' => 'expired',
            'file_path' => 'test.pdf',
        ]);

        expect($tier->isCompliant())->toBeFalse();
    });

    it('le validateur rejette un SIRET invalide via l\'algorithme de Luhn', function () {
        $validator = new App\Services\Tiers\TierValidator;
        $result = $validator->validate(['siret' => '12345678901234']); // SIRET bidon
        expect($result->fails())->toBeTrue();
    });

    it('un tiers devient non-conforme si une qualification expire', function () {
        $tier = Tiers::factory()->create();
        TierQualification::create([
            'tiers_id' => $tier->id,
            'label' => 'Qualibat 1552',
            'valid_until' => now()->subDay(),
        ]);

        expect($tier->isCompliant())->toBeFalse();
    });

    it('le service Sirene peut récupérer des données d\'entreprise', function () {
        Http::fake([
            'api.insee.fr/*' => Http::response([
                'etablissements' => [
                    [
                        'uniteLegale' => [
                            'denominationUniteLegale' => 'BATISTACK SAS',
                            'activitePrincipaleUniteLegale' => '6201Z',
                        ],
                        'adresseEtablissement' => [
                            'numeroVoieEtablissement' => '10',
                            'typeVoieEtablissement' => 'RUE',
                            'libelleVoieEtablissement' => 'DU CODE',
                            'codePostalEtablissement' => '75000',
                            'libelleCommuneEtablissement' => 'PARIS',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = app(SirenService::class);
        $data = $service->fetchCompanyData('12345678901234');

        expect($data['raison_social'])->toBe('BATISTACK SAS')
            ->and($data['code_naf'])->toBe('6201Z');
    });

    test('un sous-traitant sans attestation URSSAF obligatoire est non-conforme', function () {
        // Configurer l'exigence
        TierDocumentRequirement::create([
            'tier_type' => 'subcontractor',
            'document_type' => 'URSSAF',
            'is_mandatory' => true,
        ]);

        $tier = Tiers::factory()->create();
        $tier->types()->create(['type' => 'subcontractor']);

        expect($tier->getComplianceStatus())->toBe('non_compliant_missing');
    });

    test('on peut rattacher plusieurs contacts à un tiers', function () {
        $tier = Tiers::factory()->create();
        $tier->contacts()->create([
            'first_name' => 'Jean',
            'last_name' => 'Compta',
            'job_title' => 'Comptable',
        ]);

        expect($tier->contacts)->toHaveCount(1);
    });

    test('un tiers avec document en attente de vérification n\'est pas conforme', function () {
        $tier = Tiers::factory()->create();
        $tier->documents()->create([
            'type' => \App\Enums\Tiers\TierDocumentType::BTP_CARD->value,
            'status' => TierDocumentStatus::PendingVerification,
            'expires_at' => now()->addMonths(3),
            'file_path' => 'doc.pdf',
        ]);

        expect($tier->getComplianceStatus())->toBe(TierComplianceStatus::PendingVerification->value);
    });

    test('un tiers avec un document en attente de vérification a le statut PendingVerification', function () {
        $tier = Tiers::factory()->create();
        $tier->documents()->create([
            'type' => TierDocumentType::URSSAF->value,
            'status' => TierComplianceStatus::PendingVerification->value,
            'expires_at' => now()->addMonths(6),
            'file_path' => 'doc.pdf',
        ]);

        expect($tier->getComplianceStatus())->toBe('pending_verification');
    });

    test('le job détecte une entreprise cessée et la passe en inactive', function () {
        $tier = Tiers::factory()->create(['siret' => '12345678901234', 'status' => TierStatus::Active->value]);

        // Simuler une réponse SIRENE "Cessée" (etatAdministratif = C)
        Http::fake(['api.insee.fr/*' => Http::response(['etablissements' => [['uniteLegale' => ['etatAdministratifUniteLegale' => 'C']]]], 200)]);

        (new CheckTiersActivityJob)->handle(new SirenService);

        expect($tier->refresh()->status)->toBe(TierStatus::Inactive);
    });

    test('un document assurance doit pouvoir stocker les activités couvertes', function () {
        $tier = Tiers::factory()->create();
        $doc = TierDocument::create([
            'tiers_id' => $tier->id,
            'type' => TierDocumentType::DECENNALE,
            'montant_garantie' => 1000000,
            'activites_couvertes' => 'Maçonnerie, Charpente, Couverture',
            'expires_at' => now()->addYear(),
            'status' => TierDocumentStatus::Valid,
            'file_path' => 'assurances/decennale.pdf',
        ]);

        expect($doc->activites_couvertes)->toContain('Maçonnerie');
    });

    test('le validateur rejette un IBAN erroné via modulo 97', function () {
        $validator = new TierValidator;
        // IBAN avec une erreur volontaire
        $result = $validator->validate(['iban' => 'FR7630006000011234567890123']);
        expect($result->fails())->toBeTrue();
    });

    it('génère un code_tier unique', function () {
        $tier1 = Tiers::factory()->create();
        $tier2 = Tiers::factory()->create();

        expect($tier1->code_tiers)->not->toBe($tier2->code_tiers);
    });

    it("possède un nom d'affichage correct pour l'entité juridique", function () {
        $tier = Tiers::factory()->create([
            'type_entite' => 'personne_morale',
            'raison_social' => 'ACME Corp',
        ]);

        expect($tier->display_name)->toBe('ACME CORP');
    });

    it('has correct display name for individual', function () {
        $tier = Tiers::factory()->create([
            'type_entite' => 'personne_physique',
            'prenom' => 'Jean',
            'nom' => 'Dupont',
        ]);

        expect($tier->display_name)->toBe('Jean DUPONT');
    });

    it('can check if it has a specific type', function () {
        $tier = Tiers::factory()->create();
        $tier->types()->create(['type' => TierTypeEnum::Customer->value]);

        expect(app(TierTypeManager::class)->hasType($tier, TierTypeEnum::Customer))->toBeTrue()
            ->and(app(TierTypeManager::class)->hasType($tier, TierTypeEnum::Supplier))->toBeFalse();
    });
});

describe('TierCodeGenerator Service', function () {
    it('generates a unique code', function () {
        $tier = Tiers::factory()->create([
            'type_entite' => 'personne_morale',
            'raison_social' => 'Test Company',
            'status' => TierStatus::Active,
        ]);

        $tier->types()->create([
            'tiers_id' => $tier->id,
            'type' => \App\Enums\Tiers\TierType::Customer,
        ]);

        $generator = app(TierCodeGenerator::class);
        $code = $generator->generate($tier->types->first()->type);

        expect($code)->toMatch('/^CLI-\d{6}$/');
    });

    it('generates different codes on successive calls', function () {
        $tier = Tiers::factory()->create([
            'type_entite' => 'personne_morale',
            'raison_social' => 'Test Company',
            'status' => TierStatus::Active,
        ]);

        $tier->types()->create([
            'tiers_id' => $tier->id,
            'type' => \App\Enums\Tiers\TierType::Customer,
        ]);

        $generator = app(TierCodeGenerator::class);
        $code1 = $generator->generateWithRetry($tier->types->first()->type);
        $code2 = $generator->generateWithRetry($tier->types->first()->type);

        expect($code1)->not->toBe($code2);
    });
});

describe('TierSearchService', function () {
    it('searches by code_tiers', function () {
        Tiers::factory()->create(['code_tiers' => 'ABC-000001']);
        Tiers::factory()->create(['code_tiers' => 'XYZ-000002']);

        $service = new TierSearchService;
        $results = $service->search('ABC')->get();

        expect($results)->toHaveCount(1)
            ->and($results->first()->code_tiers)->toBe('ABC-000001');
    });

    it('searches by raison_social', function () {
        Tiers::factory()->create(['raison_social' => 'Société A']);
        Tiers::factory()->create(['raison_social' => 'Entreprise B']);

        $service = new TierSearchService;
        $results = $service->search('Société')->get();

        expect($results)->toHaveCount(1)
            ->and($results->first()->raison_social)->toBe('Société A');
    });

    it('filters by type', function () {
        $tier1 = Tiers::factory()->create();
        $tier1->types()->create(['type' => TierTypeEnum::Customer->value]);

        $tier2 = Tiers::factory()->create();
        $tier2->types()->create(['type' => TierTypeEnum::Supplier->value]);

        $service = new TierSearchService;
        $results = $service->byType(TierTypeEnum::Customer->value)->get();

        expect($results)->toHaveCount(1);
    });

    it('filters by status', function () {
        Tiers::factory()->create(['status' => TierStatus::Active]);
        Tiers::factory()->create(['status' => TierStatus::Inactive]);

        $service = new TierSearchService;
        $results = $service->active()->get();

        expect($results)->toHaveCount(1);
    });

    it('filters by entity type', function () {
        Tiers::factory()->create(['type_entite' => 'personne_morale']);
        Tiers::factory()->create(['type_entite' => 'personne_physique']);

        $service = new TierSearchService;
        $results = $service->byEntity('personne_morale')->get();

        expect($results)->toHaveCount(1);
    });

    it('loads types with query', function () {
        $tier = Tiers::factory()->create();
        $tier->types()->create(['type' => TierTypeEnum::Customer->value]);

        $service = new TierSearchService;
        $results = $service->withTypes()->get();

        expect($results->first()->types)->toHaveCount(1);
    });

    it('paginates results', function () {
        $tenant = \App\Models\Core\Tenants::factory()->create();
        Tiers::factory(20)->create(['tenants_id' => $tenant->id]);

        $service = new TierSearchService;
        $paginated = $service->paginate(10);

        expect($paginated->count())->toBe(10)
            ->and($paginated->total())->toBe(20);
    });
});

describe('TierValidator Service', function () {
    it('validates legal entity creation', function () {
        $validator = app(TierValidator::class);
        $rules = $validator->validateForCreation([
            'type_entite' => 'personne_morale',
            'raison_social' => 'Test Corp',
        ]);

        expect($rules)->toHaveKey('type_entite')
            ->and($rules)->toHaveKey('raison_social');
    });

    it('requires raison_social for legal entities', function () {
        $validator = app(TierValidator::class);
        $rules = $validator->validateForCreation(['type_entite' => 'personne_morale']);

        expect($rules['raison_social'])->toContain('required_if:type_entite,personne_morale');
    });

    it('requires nom and prenom for individuals', function () {
        $validator = app(TierValidator::class);
        $rules = $validator->validateForCreation(['type_entite' => 'personne_physique']);

        expect($rules['nom'])->toContain('required_if:type_entite,personne_physique')
            ->and($rules['prenom'])->toContain('required_if:type_entite,personne_physique');
    });

    it('validates email uniqueness on creation', function () {
        $validator = app(TierValidator::class);
        $rules = $validator->validateForCreation(['email' => 'test@example.com']);

        expect($rules['email'])->toContain('unique:tiers,email');
    });

    it('excludes current email on update', function () {
        $tier = Tiers::factory()->create();
        $validator = app(TierValidator::class);
        $rules = $validator->validateForUpdate($tier, ['email' => 'test@example.com']);

        expect($rules['email'])->toContain("unique:tiers,email,{$tier->id}");
    });

    it('validates SIRET format', function () {
        $validator = app(TierValidator::class);
        $rules = $validator->validateForCreation(['siret' => '12345678901234']);

        expect($rules['siret'])->toContain('regex:/^\d{14}$/');
    });

    it('validates code postal format', function () {
        $validator = app(TierValidator::class);
        $rules = $validator->validateForCreation(['code_postal' => '75001']);

        expect($rules['code_postal'])->toContain('regex:/^\d{5}$/');
    });
});
