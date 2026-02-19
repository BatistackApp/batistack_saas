<?php

use App\Enums\Commerce\PurchaseOrderStatus;
use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;
use App\Models\Commerce\PurchaseOrder;
use App\Models\Commerce\PurchaseOrderItem;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
});

it('calcule automatiquement les totaux HT à l\'ajout d\'un article', function () {
    $order = PurchaseOrder::factory()->create(['tenants_id' => $this->tenant->id]);

    PurchaseOrderItem::create([
        'purchase_order_id' => $order->id,
        'description' => 'Sable à bâtir',
        'quantity' => 10,
        'unit_price_ht' => 50,
        'tax_rate' => 20,
    ]);

    $order->refresh();

    expect((float) $order->total_ht)->toBe(500.0);
});

it('met à jour les stocks et le statut lors d\'une réception partielle', function () {
    $warehouse = Warehouse::factory()->create(['tenants_id' => $this->tenant->id]);
    $article = Article::factory()->create(['tenants_id' => $this->tenant->id]);

    $order = PurchaseOrder::factory()->create([
        'tenants_id' => $this->tenant->id,
        'status' => PurchaseOrderStatus::Sent,
    ]);

    $item = PurchaseOrderItem::create([
        'purchase_order_id' => $order->id,
        'article_id' => $article->id,
        'description' => 'Ciment',
        'quantity' => 100,
        'unit_price_ht' => 5,
        'tax_rate' => 20,
    ]);

    // Action : Réception de 40 sacs
    $response = $this->actingAs($this->user)
        ->postJson("/api/commerce/purchase/{$order->id}/receive", [
            'warehouse_id' => $warehouse->id,
            'delivery_note_ref' => 'BL896632026',
            'items' => [
                ['item_id' => $item->id, 'quantity' => 40],
            ],
        ]);

    $response->assertStatus(200);

    // Vérification du statut de la commande
    expect($order->refresh()->status)->toBe(PurchaseOrderStatus::PartiallyReceived);

    // Vérification du stock physique (via pivot article_warehouse)
    $stock = $article->warehouses()->where('warehouse_id', $warehouse->id)->first()->pivot->quantity;
    expect((float) $stock)->toBe(40.0);
});
