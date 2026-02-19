<?php

use App\Exceptions\GED\QuotaExceededException;
use App\Models\Core\Tenants;
use App\Models\GED\Document;
use App\Models\HR\Employee;
use App\Models\Projects\Project;
use App\Models\User;
use App\Services\GED\GEDService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    // Création du tenant et de l'utilisateur de test
    $this->tenant = Tenants::factory()->create(['storage_used' => 0]);
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);

    // On agit en tant que cet utilisateur pour le contexte tenant
    $this->actingAs($this->user);
    $this->gedService = app(GEDService::class);
});

/*
|--------------------------------------------------------------------------
| Tests de Polymorphisme
|--------------------------------------------------------------------------
*/
describe('GED - Polymorphisme', function () {
    it('peut attacher un document à un Projet (Polymorphisme)', function () {
        $project = Project::factory()->create(['tenants_id' => $this->tenant->id]);
        $file = UploadedFile::fake()->create('devis_materiaux.pdf', 500);

        $document = $this->gedService->uploadForResource($file, $project);

        expect($document->documentable_type)->toBe(Project::class)
            ->and($document->documentable_id)->toBe($project->id)
            ->and(Storage::disk('public')->exists($document->file_path))->toBeTrue();
    });

    it('peut attacher un document à un Employé (Contrat de travail)', function () {
        $employee = Employee::factory()->create(['tenants_id' => $this->tenant->id]);
        $file = UploadedFile::fake()->create('contrat.pdf', 1000);

        $document = $this->gedService->uploadForResource($file, $employee);

        expect($document->documentable_type)->toBe(Employee::class)
            ->and($document->documentable_id)->toBe($employee->id);

        // Vérification de la structure du chemin S3 spécifiée dans les besoins
        $expectedPath = "tenants/{$this->tenant->id}/employees/{$employee->id}/contrat.pdf";
        expect($document->file_path)->toBe($expectedPath);
    });
});

/*
|--------------------------------------------------------------------------
| Tests d'Arborescence Virtuelle
|--------------------------------------------------------------------------
*/

describe('GED - Arborescence Virtuelle', function () {
    it('supprime récursivement les fichiers S3 lors de la suppression d\'un dossier', function () {
        $folder = \App\Models\GED\DocumentFolder::create(['tenants_id' => $this->tenant->id, 'name' => 'Archives']);
        $file = UploadedFile::fake()->create('archive_2023.zip', 2000);

        $document = $this->gedService->uploadDocument($file, ['folder_id' => $folder->id, 'file_name' => 'archive_2023.zip']);

        $this->gedService->deleteFolder($folder, $this->tenant);

        $document->delete();

        // Le dossier, le document en DB et le fichier S3 doivent disparaître
        $this->assertDatabaseMissing('document_folders', ['id' => $folder->id]);
        Storage::disk('public')->assertMissing($document->file_path);
    });
});

/*
|--------------------------------------------------------------------------
| Tests de Quotas et Limites (SAAS)
|--------------------------------------------------------------------------
*/
describe('GED - Quotas et Limites (SAAS)', function () {
    it('bloque l\'upload si le quota du tenant est dépassé', function () {
        // On simule un tenant qui a déjà consommé 10 Go (limite théorique du plan)
        $this->tenant->update(['storage_used' => 10 * 1024 * 1024 * 1024]);

        $file = UploadedFile::fake()->create('gros_fichier.iso', 100 * 1024); // 100 Mo

        $this->expectException(QuotaExceededException::class);

        $this->gedService->uploadDocument($file, []);
    });

    it('incrémente le stockage utilisé du tenant après un upload réussi', function () {
        $initialUsage = $this->tenant->storage_used;
        $file = UploadedFile::fake()->create('test.txt', 100); // 100 KB

        $this->gedService->uploadDocument($file, []);

        $this->tenant->refresh();
        expect($this->tenant->storage_used)->toBe($initialUsage + (100 * 1024));
    });
});
