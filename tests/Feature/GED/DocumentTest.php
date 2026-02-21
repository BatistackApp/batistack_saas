<?php

use App\Enums\GED\DocumentStatus;
use App\Enums\GED\DocumentType;
use App\Exceptions\GED\QuotaExceededException;
use App\Jobs\GED\GenerateThumbnailJob;
use App\Models\Core\Tenants;
use App\Models\GED\Document;
use App\Models\GED\DocumentFolder;
use App\Models\Projects\Project;
use App\Models\User;
use App\Services\GED\GEDService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Queue::fake();

    // 1. Setup Tenant A
    $this->tenant = Tenants::factory()->create(['storage_used' => 0]);
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);

    // 2. Setup Tenant B (pour les tests de sécurité)
    $this->otherTenant = Tenants::factory()->create();
    $this->otherUser = User::factory()->create(['tenants_id' => $this->otherTenant->id]);

    $this->gedService = app(GEDService::class);
    $this->actingAs($this->user);
});

/*
|--------------------------------------------------------------------------
| TESTS DE SERVICE & LOGIQUE MÉTIER
|--------------------------------------------------------------------------
*/

describe('GED Service - Logique Métier', function () {

    it('télécharge un document et crée l\'entrée en base de données', function () {
        $file = UploadedFile::fake()->create('plan_fondation.dwg', 1024);

        // CORRECTION : On passe l'Enum DocumentType directement, pas un tableau
        $document = $this->gedService->upload(
            $file,
            DocumentType::Plan,
            null,
            null,
            ['description' => 'Plan de test']
        );

        expect($document)->toBeInstanceOf(Document::class)
            ->and($document->name)->toBe('plan_fondation.dwg')
            ->and($document->type)->toBe(DocumentType::Plan)
            ->and($document->tenants_id)->toBe($this->tenant->id);

        Storage::disk('public')->assertExists($document->file_path);

        Queue::assertPushed(GenerateThumbnailJob::class, function ($job) use ($document) {
            return $job->document->id === $document->id;
        });
    });

    it('bloque l\'upload si le quota du tenant est dépassé', function () {
        // Simuler un stockage plein (Limite par défaut 5Go dans QuotaService)
        $this->tenant->update(['storage_used' => 6 * 1024 * 1024 * 1024]);

        $file = UploadedFile::fake()->create('devis.pdf', 100);

        // On s'assure de passer les bons arguments pour éviter le TypeError
        expect(fn () => $this->gedService->upload($file, DocumentType::Other))
            ->toThrow(QuotaExceededException::class);
    });

    it('supprime physiquement le fichier lors de la suppression d\'un document', function () {
        $file = UploadedFile::fake()->create('photo_chantier.jpg', 500);
        $document = $this->gedService->upload($file, DocumentType::Photo);
        $path = $document->file_path;

        // CORRECTION : La méthode dans GEDService est delete(), pas deleteDocument()
        $this->gedService->deleteDocument($document);

        expect(Document::find($document->id))->toBeNull();
        Storage::disk('public')->assertMissing($path);
    });

    it('peut attacher un document à une ressource (Polymorphisme)', function () {
        $project = Project::factory()->create(['tenants_id' => $this->tenant->id]);
        $file = UploadedFile::fake()->create('contrat.pdf', 200);

        // Utilisation du paramètre $documentable du service upload
        $document = $this->gedService->upload($file, DocumentType::Contract, $project);

        expect($document->documentable_type)->toBe(Project::class)
            ->and($document->documentable_id)->toBe($project->id);
    });

});

/*
|--------------------------------------------------------------------------
| TESTS API (CONTROLLER & ROUTES)
|--------------------------------------------------------------------------
*/

describe('GED API - Endpoints & Navigation', function () {

    it('liste les documents du dossier racine du tenant', function () {
        Document::factory()->count(3)->create([
            'tenants_id' => $this->tenant->id,
            'folder_id' => null,
            'status' => DocumentStatus::Validated,
        ]);

        $response = $this->getJson(route('ged.index'));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'documents.data');
    });

    it('filtre les documents par type BTP', function () {
        Document::factory()->create(['tenants_id' => $this->tenant->id, 'type' => DocumentType::Plan]);
        Document::factory()->create(['tenants_id' => $this->tenant->id, 'type' => DocumentType::Invoice]);

        $response = $this->getJson(route('ged.index', ['type' => DocumentType::Plan->value]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'documents.data')
            ->assertJsonPath('documents.data.0.type', DocumentType::Plan->value);
    });

    it('permet de créer un dossier', function () {
        $response = $this->postJson(route('ged.folders.store'), [
            'name' => 'Plans EXE',
            'color' => '#FF0000',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('document_folders', [
            'name' => 'Plans EXE',
            'tenants_id' => $this->tenant->id,
        ]);
    });

    it('effectue des actions de masse (Archivage)', function () {
        $docs = Document::factory()->count(5)->create(['tenants_id' => $this->tenant->id]);
        $ids = $docs->pluck('id')->toArray();

        $response = $this->postJson(route('ged.documents.bulk'), [
            'document_ids' => $ids,
            'action' => 'archive',
        ]);

        $response->assertStatus(200);
        expect(Document::where('status', DocumentStatus::Archived)->count())->toBe(5);
    });

});

/*
|--------------------------------------------------------------------------
| TESTS DE SÉCURITÉ (ISOLATION TENANT)
|--------------------------------------------------------------------------
*/

describe('GED Sécurité - Isolation des Données', function () {

    it('interdit l\'accès à un document d\'un autre tenant', function () {
        $otherDoc = Document::factory()->create(['tenants_id' => $this->otherTenant->id]);

        $response = $this->getJson(route('ged.documents.download', $otherDoc));

        // Le scope HasTenant devrait soit retourner 404, soit votre logic 403
        expect($response->status())->toBeIn([403, 404]);
    });

    it('n\'affiche pas les dossiers des autres tenants dans l\'index', function () {
        DocumentFolder::factory()->create(['tenants_id' => $this->otherTenant->id, 'name' => 'Secret Folder']);

        $response = $this->getJson(route('ged.index'));

        $response->assertStatus(200)
            ->assertJsonMissing(['name' => 'Secret Folder']);
    });

});
