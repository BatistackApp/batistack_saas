<?php

use App\Enums\Commerce\InvoiceStatus;
use App\Models\Commerce\Invoices;
use App\Models\Commerce\Quote;
use App\Models\Commerce\QuoteItem;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->customer = Tiers::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->project = Project::factory()->create(['tenants_id' => $this->tenant->id]);
});

it('calcule le delta HT correctement entre deux situations successives', function () {
    // 1. Création d'un devis de 10 000€
    $quote = Quote::factory()->create([
        'tenants_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'project_id' => $this->project->id,
    ]);

    $quoteItem = QuoteItem::create([
        'quote_id' => $quote->id,
        'label' => 'Maçonnerie',
        'quantity' => 1,
        'unit_price_ht' => 10000,
    ]);

    // 2. Situation n°1 : 30% d'avancement
    $this->actingAs($this->user)->postJson('/api/commerce/invoices/progress', [
        'quote_id' => $quote->id,
        'situation_number' => 1,
        'progress_data' => [
            ['quote_item_id' => $quoteItem->id, 'progress_percentage' => 30],
        ],
    ]);

    $sit1 = Invoices::where('situation_number', 1)->first();
    // On valide la sit1 pour qu'elle soit prise en compte dans le calcul de la sit2
    $sit1->update(['status' => InvoiceStatus::Validated]);

    // 3. Situation n°2 : 70% d'avancement (Cumulé)
    // Le montant de la période doit être (10000 * 0.7) - 3000 = 4000 € HT
    $response = $this->actingAs($this->user)->postJson('/api/commerce/invoices/progress', [
        'quote_id' => $quote->id,
        'situation_number' => 2,
        'progress_data' => [
            ['quote_item_id' => $quoteItem->id, 'progress_percentage' => 70],
        ],
    ]);

    $response->assertStatus(201);

    $sit2 = Invoices::where('situation_number', 2)->first();
    expect((float) $sit2->total_ht)->toBe(4000.0);
});

it('applique la retenue de garantie de 5% sur le TTC', function () {
    $quote = Quote::factory()->create(['tenants_id' => $this->tenant->id]);
    $quoteItem = QuoteItem::create([
        'quote_id' => $quote->id,
        'label' => 'Test RG',
        'quantity' => 1,
        'unit_price_ht' => 1000,
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/commerce/invoices/progress', [
        'quote_id' => $quote->id,
        'situation_number' => 1,
        'progress_data' => [
            ['quote_item_id' => $quoteItem->id, 'progress_percentage' => 100],
        ],
    ]);

    $invoice = Invoices::latest()->first();

    // Total HT: 1000, TVA: 200 -> TTC: 1200
    // RG (5% de 1200) = 60€
    expect((float) $invoice->retenue_garantie_amount)->toBe(60.0)
        ->and((float) $invoice->net_to_pay)->toBe(1140.0);
    // 1200 - 60
});
