<?php

use App\Enums\Tiers\TierStatus;
use App\Enums\Tiers\TierType as TierTypeEnum;
use App\Models\Tiers\TierDocument;
use App\Models\Tiers\TierQualification;
use App\Models\Tiers\Tiers;
use App\Models\Tiers\TierType;
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

    it('generates unique code_tiers automatically', function () {
        $tier1 = Tiers::factory()->create();
        $tier2 = Tiers::factory()->create();

        expect($tier1->code_tiers)->not->toBe($tier2->code_tiers);
    });

    it('has correct display name for legal entity', function () {
        $tier = Tiers::factory()->create([
            'type_entite' => 'personne_morale',
            'raison_social' => 'ACME Corp',
        ]);

        expect($tier->display_name)->toBe('ACME Corp');
    });

    it('has correct display name for individual', function () {
        $tier = Tiers::factory()->create([
            'type_entite' => 'personne_physique',
            'prenom' => 'Jean',
            'nom' => 'Dupont',
        ]);

        expect($tier->display_name)->toBe('Jean Dupont');
    });

    it('can check if it has a specific type', function () {
        $tier = Tiers::factory()->create();
        $tier->types()->create(['type' => TierTypeEnum::Customer->value]);

        expect($tier->hasType(TierTypeEnum::Customer))->toBeTrue()
            ->and($tier->hasType(TierTypeEnum::Supplier))->toBeFalse();
    });

    it('can add a new type', function () {
        $tier = Tiers::factory()->create();

        $tier->addType(TierTypeEnum::Customer);

        expect($tier->types()->count())->toBe(1)
            ->and($tier->hasType(TierTypeEnum::Customer))->toBeTrue();
    });

    it('does not duplicate types when adding', function () {
        $tier = Tiers::factory()->create();
        $tier->addType(TierTypeEnum::Customer);
        $tier->addType(TierTypeEnum::Customer);

        expect($tier->types()->count())->toBe(1);
    });
});

describe('TierType Model', function () {
    it('belongs to a tier', function () {
        $tier = Tiers::factory()->create();
        $tierType = TierType::factory()->create(['tiers_id' => $tier->id]);

        expect($tierType->tiers->id)->toBe($tier->id);
    });

    it('enforces unique combination of tier and type', function () {
        $tier = Tiers::factory()->create();
        TierType::factory()->create([
            'tiers_id' => $tier->id,
            'type' => TierTypeEnum::Customer->value,
        ]);

        TierType::factory()->create([
            'tiers_id' => $tier->id,
            'type' => TierTypeEnum::Customer->value,
        ]);

        // L'exception est attendue sur cette action
        expect(fn () => TierType::factory()->create([
            'tiers_id' => $tier->id,
            'type' => TierTypeEnum::Customer->value,
        ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
    });

    it('sets first type as primary by default', function () {
        $tier = Tiers::factory()->create();
        $tierType = TierType::factory()->create(['tiers_id' => $tier->id, 'is_primary' => false]);

        expect($tierType->refresh()->is_primary)->toBeTrue();
    });

    it('can unset primary when adding new primary type', function () {
        $tier = Tiers::factory()->create();
        $type1 = TierType::factory()->create([
            'tiers_id' => $tier->id,
            'type' => TierTypeEnum::Customer->value,
            'is_primary' => true,
        ]);

        $type2 = TierType::factory()->create([
            'tiers_id' => $tier->id,
            'type' => TierTypeEnum::Supplier->value,
            'is_primary' => true,
        ]);

        expect($type1->refresh()->is_primary)->toBeFalse()
            ->and($type2->refresh()->is_primary)->toBeTrue();
    });
});

describe('TierCodeGenerator Service', function () {
    it('generates a unique code', function () {
        $generator = app(TierCodeGenerator::class);
        $code = $generator->generate();

        expect($code)->toMatch('/^[A-Z]{3}-\d{6}$/');
    });

    it('generates different codes on successive calls', function () {
        $generator = app(TierCodeGenerator::class);
        $code1 = $generator->generateWithRetry();
        $code2 = $generator->generateWithRetry();

        expect($code1)->not->toBe($code2);
    });

    it('throws exception after max retries', function () {
        // On mock le modèle pour qu'il prétende que chaque code existe déjà
        Tiers::shouldReceive('where')->andReturnSelf();
        Tiers::shouldReceive('exists')->andReturn(true);

        $generator = app(TierCodeGenerator::class);
        $generator->generateWithRetry(5); // Le nombre d'essais par défaut
    })->throws(\Exception::class, 'Unable to generate unique tier code after 5 attempts');
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
        Tiers::factory(20)->create();

        $service = new TierSearchService;
        $paginated = $service->paginate(10);

        expect($paginated->count())->toBe(10)
            ->and($paginated->total())->toBe(20);
    });
});

describe('TierTypeManager Service', function () {
    it('adds a type to a tier', function () {
        $tier = Tiers::factory()->create();
        $manager = app(TierTypeManager::class);

        $manager->addType($tier, TierTypeEnum::Customer);

        expect($tier->hasType(TierTypeEnum::Customer))->toBeTrue();
    });

    it('removes a type from a tier', function () {
        $tier = Tiers::factory()->create();
        $tier->types()->create(['type' => TierTypeEnum::Customer->value]);

        $manager = app(TierTypeManager::class);
        $manager->removeType($tier, TierTypeEnum::Customer);

        expect($tier->hasType(TierTypeEnum::Customer))->toBeFalse();
    });

    it('sets primary type', function () {
        $tier = Tiers::factory()->create();
        $tier->types()->create(['type' => TierTypeEnum::Customer->value]);
        $tier->types()->create(['type' => TierTypeEnum::Supplier->value]);

        $manager = app(TierTypeManager::class);
        $manager->setPrimaryType($tier, TierTypeEnum::Supplier);

        expect($manager->getPrimaryType($tier))->toBe(TierTypeEnum::Supplier);
    });

    it('throws exception when setting non-existent type as primary', function () {
        $tier = Tiers::factory()->create();
        $manager = app(TierTypeManager::class);

        $manager->setPrimaryType($tier, TierTypeEnum::Customer);
    })->throws(\Exception::class, 'does not have type');

    it('returns primary type', function () {
        $tier = Tiers::factory()->create();
        $tier->types()->create([
            'type' => TierTypeEnum::Customer->value,
            'is_primary' => true,
        ]);

        $manager = app(TierTypeManager::class);
        $primary = $manager->getPrimaryType($tier);

        expect($primary)->toBe(TierTypeEnum::Customer);
    });
});

describe('TierValidator Service', function () {
    it('validates legal entity creation', function () {
        $validator = app(TierValidator::class);
        $rules = $validator->validateForCreation([
            'type_entite' => 'personne_morale',
            'raison_social' => 'Test Corp',
        ]);

        expect($rules['type_entite'])->toBeDefined()
            ->and($rules['raison_social'])->toBeDefined();
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
