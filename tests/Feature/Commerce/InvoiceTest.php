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
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->customer = Tiers::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->project = Project::factory()->create(['tenants_id' => $this->tenant->id]);
});

it('calcule les cumuls et le net de période selon le FinancialCalculatorService', function () {
    // 1. Devis de 10 000€ HT
    $quote = Quote::factory()->create([
        'tenants_id' => $this->tenant->id,
        'customer_id' => $this->customer->id,
        'project_id' => $this->project->id,
    ]);

    $quoteItem = QuoteItem::create([
        'quote_id' => $quote->id,
        'label' => 'Gros Oeuvre',
        'quantity' => 1,
        'unit_price_ht' => 10000,
        'tax_rate' => 20.00,
    ]);

    // 2. Situation n°1 : 30% (3000€ HT)
    $this->actingAs($this->user)->postJson('/api/commerce/invoices/progress', [
        'quote_id' => $quote->id,
        'situation_number' => 1,
        'progress_data' => [
            ['quote_item_id' => $quoteItem->id, 'progress_percentage' => 30],
        ],
    ]);

    $sit1 = Invoices::where('situation_number', 1)->first();
    $sit1->update(['status' => InvoiceStatus::Validated]);

    // 3. Situation n°2 : 100% (70% de delta = 7000€ HT)
    $this->actingAs($this->user)->postJson('/api/commerce/invoices/progress', [
        'quote_id' => $quote->id,
        'situation_number' => 2,
        'progress_data' => [
            ['quote_item_id' => $quoteItem->id, 'progress_percentage' => 100],
        ],
    ]);

    $sit2 = Invoices::where('situation_number', 2)->latest()->first();

    // Vérification HT Période
    expect((float) $sit2->total_ht)->toBe(7000.0)
        ->and((float) $sit2->total_tva)->toBe(1400.0);
});

it('déduit correctement la retenue de garantie du net à payer', function () {
    $quote = Quote::factory()->create(['tenants_id' => $this->tenant->id]);
    $item = QuoteItem::create([
        'quote_id' => $quote->id,
        'label' => 'Menuiserie',
        'quantity' => 1,
        'unit_price_ht' => 1000,
        'tax_rate' => 20.00,
    ]);

    $this->actingAs($this->user)->postJson('/api/commerce/invoices/progress', [
        'quote_id' => $quote->id,
        'situation_number' => 1,
        'progress_data' => [['quote_item_id' => $item->id, 'progress_percentage' => 100]],
    ]);

    $invoice = Invoices::latest()->first();

    // Montant TTC : 1200.00
    // RG (5% de 1200) : 60.00
    // Net à payer : 1140.00
    expect((float) $invoice->total_ttc)->toBe(1200.0)
        ->and((float) $invoice->retenue_garantie_amount)->toBe(60.0)
        ->and((float) $invoice->net_to_pay)->toBe(1140.0);
});

it('permet de réinitialiser l\'avancement d\'un devis via un avoir', function () {
    $tenant = Tenants::factory()->create();
    $user = User::factory()->create(['tenants_id' => $tenant->id]);

    // 1. Création d'un devis (10 000€)
    $quote = Quote::factory()->create(['tenants_id' => $tenant->id]);
    $item = QuoteItem::create([
        'quote_id' => $quote->id,
        'label' => 'Maçonnerie',
        'quantity' => 1,
        'unit_price_ht' => 10000,
        'tax_rate' => 20
    ]);

    // 2. Création Situation n°1 (50% = 5000€)
    $this->actingAs($user)->postJson('/api/commerce/invoices/progress', [
        'quote_id' => $quote->id,
        'situation_number' => 1,
        'progress_data' => [['quote_item_id' => $item->id, 'progress_percentage' => 50]]
    ]);

    $sit1 = Invoices::where('situation_number', 1)->first();
    // On valide la situation pour pouvoir créer l'avoir
    $this->actingAs($user)->postJson("/api/commerce/invoices/{$sit1->id}/validate");
    $sit1->refresh();

    // 3. Création de l'Avoir pour annuler la Sit 1
    $this->actingAs($user)->postJson("/api/commerce/invoices/{$sit1->id}/credit-note");

    $avoir = Invoices::where('type', InvoiceType::CreditNote)->first();
    expect((float) $avoir->total_ht)->toBe(-5000.0);

    // Validation de l'avoir
    $this->actingAs($user)->postJson("/api/commerce/invoices/{$avoir->id}/validate");

    // 4. Vérification : Si je recrée une situation n°1 (rectifiée) à 30%
    // Le montant facturé doit être 3000€ (car Cumul précédent = 5000 - 5000 = 0)
    $response = $this->actingAs($user)->postJson('/api/commerce/invoices/progress', [
        'quote_id' => $quote->id,
        'situation_number' => 1,
        'progress_data' => [['quote_item_id' => $item->id, 'progress_percentage' => 30]]
    ]);

    $sit1Bis = Invoices::where('situation_number', 1)->latest()->first();
    expect((float) $sit1Bis->total_ht)->toBe(3000.0);
});
