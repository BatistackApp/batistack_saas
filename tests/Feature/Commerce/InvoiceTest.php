<?php

use App\Enums\Commerce\InvoiceStatus;
use App\Enums\Commerce\InvoiceType;
use App\Models\Commerce\Invoices;
use App\Models\Commerce\Quote;
use App\Models\Commerce\QuoteItem;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Services\Commerce\InvoicingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->customer = Tiers::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->project = Project::factory()->create(['tenants_id' => $this->tenant->id]);
});

it('liste les factures du tenant avec pagination', function () {
    Invoices::factory()->count(3)->create([
        'tenants_id' => $this->tenant->id,
        'tiers_id' => $this->customer->id,
        'project_id' => $this->project->id,
    ]);

    // Facture d'un autre tenant (ne doit pas être visible)
    $otherTenant = Tenants::factory()->create();
    Invoices::factory()->create(['tenants_id' => $otherTenant->id]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/commerce/invoices');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['data', 'links']);
});

it('filtre les factures par projet', function () {
    $otherProject = Project::factory()->create(['tenants_id' => $this->tenant->id]);

    Invoices::factory()->create([
        'tenants_id' => $this->tenant->id,
        'project_id' => $this->project->id,
    ]);

    Invoices::factory()->create([
        'tenants_id' => $this->tenant->id,
        'project_id' => $otherProject->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/commerce/invoices?project_id=' . $this->project->id);

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.project_id', $this->project->id);
});

it('valide une facture via le service', function () {
    $invoice = Invoices::factory()->create([
        'tenants_id' => $this->tenant->id,
        'status' => InvoiceStatus::Draft,
    ]);

    // Mock du service de validation
    $this->mock(InvoicingService::class, function (MockInterface $mock) use ($invoice) {
        $mock->shouldReceive('validateInvoice')
            ->with(Mockery::on(fn($arg) => $arg->id === $invoice->id))
            ->once()
            ->andReturn($invoice->fill([
                'status' => InvoiceStatus::Validated,
                'reference' => 'FAC-FINAL-001'
            ]));
    });

    $response = $this->actingAs($this->user)
        ->postJson("/api/commerce/invoices/{$invoice->id}/validate");

    $response->assertStatus(200)
        ->assertJsonPath('status', InvoiceStatus::Validated->value)
        ->assertJsonPath('reference', 'FAC-FINAL-001');
});
