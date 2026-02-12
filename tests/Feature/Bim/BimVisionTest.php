<?php

use App\Enums\Bim\BimModelStatus;
use App\Jobs\Bim\ProcessBimModelJob;
use App\Models\Articles\Article;
use App\Models\Bim\BimModel;
use App\Models\Bim\BimObject;
use App\Models\Projects\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'bim.manage', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'bim.view', 'guard_name' => 'web']);

    $this->tenant = \App\Models\Core\Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->user->givePermissionTo(['bim.manage', 'bim.view']);
    $this->tenantsId = $this->tenant->id;

    $this->project = Project::factory()->create(['tenants_id' => $this->tenantsId]);

    Storage::fake('public');
    Queue::fake();
});

test('un utilisateur peut uploader une maquette IFC et déclencher le job de traitement', function () {
    $file = UploadedFile::fake()->create('maquette_structure.ifc', 1024); // 1 Mo

    $response = $this->actingAs($this->user)
        ->postJson(route('bim-models.store'), [
            'project_id' => $this->project->id,
            'name' => 'Maquette Gros Œuvre',
            'ifc_file' => $file,
        ]);

    $response->assertStatus(201);

    $model = BimModel::first();
    expect($model->name)->toBe('Maquette Gros Œuvre')
        ->and($model->status)->toBe(BimModelStatus::UPLOADING);

    // Vérifier que le fichier est sur S3
    Storage::disk('public')->assertExists($model->file_path);

    // Vérifier que le Job de parsing a été dispatché (via l'Observer)
    Queue::assertPushed(ProcessBimModelJob::class, function ($job) use ($model) {
        return $job->model->id === $model->id;
    });
});

test('on peut récupérer le contexte métier d\'un objet via son GUID IFC', function () {
    $model = BimModel::factory()->create([
        'tenants_id' => $this->tenantsId,
        'project_id' => $this->project->id,
        'status' => BimModelStatus::READY,
    ]);

    $object = BimObject::create([
        'bim_model_id' => $model->id,
        'guid' => '3Y_8v$p9H4xR0Yp_X8v_123',
        'ifc_type' => 'IfcWall',
        'label' => 'Mur Porteur Béton',
        'properties' => ['Material' => 'Béton Armé', 'Thickness' => '20cm'],
    ]);

    // On lie cet objet 3D à un article du stock (Mapping)
    $article = Article::factory()->create(['tenants_id' => $this->tenantsId, 'name' => 'Béton B25']);
    $object->mappings()->create([
        'bim_object_id' => $object->id,
        'mappable_id' => $article->id,
        'mappable_type' => Article::class,
        'color_override' => '#00FF00',
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/bim-models/{$model->id}/objects/{$object->guid}/context");

    $response->assertStatus(200)
        ->assertJsonPath('found', true)
        ->assertJsonPath('object.label', 'Mur Porteur Béton')
        ->assertJsonPath('linked_resources.0.data.name', 'Béton B25');
});

test('on peut enregistrer et restaurer un point de vue caméra (Snapshot)', function () {
    $model = BimModel::factory()->create(['tenants_id' => $this->tenantsId]);

    $cameraState = [
        'position' => ['x' => 10, 'y' => 5, 'z' => 20],
        'target' => ['x' => 0, 'y' => 0, 'z' => 0],
    ];

    $response = $this->actingAs($this->user)
        ->postJson("/api/bim-models/{$model->id}/views", [
            'bim_model_id' => $model->id,
            'name' => 'Vue Défaut Étanchéité',
            'camera_state' => $cameraState,
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('bim_views', [
        'name' => 'Vue Défaut Étanchéité',
        'user_id' => $this->user->id,
    ]);
});

test('le système respecte l\'isolation multi-tenant pour les maquettes 3D', function () {
    // Création d'une maquette pour un AUTRE tenant
    $otherTenant = \App\Models\Core\Tenants::factory()->create();
    $otherModel = BimModel::factory()->create(['tenants_id' => $otherTenant->id]);

    // L'utilisateur actuel ne doit pas pouvoir y accéder
    $response = $this->actingAs($this->user)
        ->getJson(route('bim-models.show', $otherModel));

    // Le GlobalScope Tenant doit retourner une 404
    $response->assertStatus(404);
});
