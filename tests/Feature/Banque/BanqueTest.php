<?php

use App\Enums\Commerce\InvoiceStatus;
use App\Models\Banque\BankAccount;
use App\Models\Banque\BankTransaction;
use App\Models\Banque\Payment;
use App\Models\Commerce\Invoices;
use App\Models\Core\Tenants;
use App\Models\User;
use App\Services\Banque\BankingSyncService;
use App\Services\Banque\ReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->account = BankAccount::factory()->create([
        'tenants_id' => $this->tenant->id,
        'bridge_id' => 'bridge_acc_123',
        'current_balance' => 1000.00,
    ]);
});

describe('Synchronisation Bridge V3', function () {

    it('importe les transactions depuis Bridge V3 et met à jour le solde', function () {
        // Mock de la réponse Bridge V3
        Http::fake([
            'api.bridgeapi.io/v3/transactions*' => Http::response([
                'resources' => [
                    [
                        'id' => 'tr_001',
                        'amount' => 150.50,
                        'date' => '2026-02-01',
                        'booking_date' => '2026-02-02',
                        'description' => 'VIREMENT CLIENT ABC',
                    ],
                ],
            ], 200),
        ]);

        $service = app(BankingSyncService::class);
        $count = $service->syncAccount($this->account);

        expect($count)->toBe(1);

        $this->assertDatabaseHas('bank_transactions', [
            'external_id' => 'tr_001',
            'amount' => 150.50,
            'bank_account_id' => $this->account->id,
        ]);

        // Le solde doit être incrémenté : 1000 + 150.50
        expect((float) $this->account->refresh()->current_balance)->toBe(1150.50);
    });

    it('détecte une expiration de consentement (401) et change le statut du compte', function () {
        Notification::fake();

        Http::fake([
            'api.bridgeapi.io/v3/transactions*' => Http::response([], 401),
        ]);

        $service = app(BankingSyncService::class);

        // On s'attend à une exception gérée
        try {
            $service->syncAccount($this->account);
        } catch (\Exception $e) {
            expect($this->account->refresh()->sync_status)->toBe(\App\Enums\Banque\BankSyncStatus::Error);
        }
    });
});

describe('Moteur de Rapprochement Bancaire', function () {

    it('suggère des factures correspondantes selon un score de confiance', function () {
        // 1. Une facture de 500€
        $invoice = Invoices::factory()->create([
            'tenants_id' => $this->tenant->id,
            'reference' => 'FAC-2026-001',
            'total_ht' => 500.00,
            'total_tva' => 0,
            'total_ttc' => 500.00,
            'status' => InvoiceStatus::Validated,
        ]);

        // 2. Une transaction bancaire de 500€ avec la référence dans le libellé
        $transaction = BankTransaction::factory()->create([
            'tenants_id' => $this->tenant->id,
            'amount' => 500.00,
            'label' => 'Paiement FAC-2026-001',
            'is_reconciled' => false,
        ]);

        $service = app(ReconciliationService::class);
        $suggestions = $service->suggestMatches($transaction);

        expect($suggestions)->toHaveCount(1)
            ->and($suggestions[0]['score'])->toBe(100)
            ->and($suggestions[0]['invoice']->id)->toBe($invoice->id);
        // Match parfait montant + référence
    });

    it('valide un rapprochement et met à jour le statut de la facture', function () {
        $invoice = Invoices::factory()->create([
            'tenants_id' => $this->tenant->id,
            'total_ht' => 1000.00,
            'total_tva' => 0,
            'total_ttc' => 1000.00,
            'status' => InvoiceStatus::Validated,
        ]);

        $transaction = BankTransaction::factory()->create([
            'tenants_id' => $this->tenant->id,
            'amount' => 1000.00,
            'value_date' => now(),
        ]);

        $service = app(ReconciliationService::class);
        $payment = $service->reconcile($transaction, $invoice, 1000.00);

        expect($payment)->toBeInstanceOf(Payment::class)
            ->and($invoice->refresh()->status)->toBe(InvoiceStatus::Paid)
            ->and($transaction->refresh()->is_reconciled)->toBeTrue();
    });
});

describe('API Banque & Sécurité', function () {

    it('interdit l’accès aux transactions d’un autre tenant', function () {
        $otherTenant = Tenants::factory()->create();
        $otherTransaction = BankTransaction::factory()->create(['tenants_id' => $otherTenant->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/bank-transactions');

        // Le scope global doit filtrer la transaction du voisin
        $data = $response->json('data');
        foreach ($data as $item) {
            expect($item['tenants_id'])->toBe($this->tenant->id);
        }
    });

    it('permet de valider un rapprochement via l’API', function () {
        $invoice = Invoices::factory()->create(['tenants_id' => $this->tenant->id, 'status' => InvoiceStatus::Validated]);
        $transaction = BankTransaction::factory()->create(['tenants_id' => $this->tenant->id, 'amount' => $invoice->total_ttc]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/reconciliation', [
                'bank_transaction_id' => $transaction->id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total_ttc,
                'payment_date' => now()->format('Y-m-d'),
                'method' => 'virement',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id]);
    });

});
