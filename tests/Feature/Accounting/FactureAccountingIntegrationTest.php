<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Accounting\AccountingAccounts;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingJournal;
use App\Models\Commerce\Facture;
use App\Models\Core\Tenant;
use App\Models\User;
use App\Observers\Commerce\FactureObserver;

uses(RefreshDatabase::class);

describe("Facture Accounting Integration", function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();
        $this->chantier = \App\Models\Chantiers\Chantier::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->user = User::factory()->create();

        // Créer les comptes comptables nécessaires
        AccountingAccounts::factory()->create([
            'tenant_id' => $this->tenant->id,
            'number' => '701001',
            'name' => 'Ventes de services',
        ]);

        AccountingAccounts::factory()->create([
            'tenant_id' => $this->tenant->id,
            'number' => '44571',
            'name' => 'TVA collectée',
        ]);

        // Créer le journal de ventes
        AccountingJournal::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'VT',
            'type' => 'VT',
        ]);
    });

    it('crée une écriture comptable lors de la validation de facture', function () {
        $facture = Facture::factory()->create([
            'tenant_id' => $this->tenant->id,
            'chantier_id' => $this->chantier->id,
            'status' => 'posted',
            'montant_ht' => 1000,
            'montant_tva' => 200,
            'montant_ttc' => 1200,
        ]);

        $customer = $facture->tiers;
        $customer->accountingAccount()->associate(
            AccountingAccounts::factory()->create([
                'tenant_id' => $this->tenant->id,
                'number' => '411001',
            ])
        )->save();

        $observer = app(FactureObserver::class);
        $observer->updated($facture);

        $entry = AccountingEntry::where('source_type', 'invoice')
            ->where('source_id', $facture->id)
            ->first();

        expect($entry)->not()->toBeNull()
            ->and($entry->lines)->toHaveCount(3);
    });

    it('l\'écriture comptable de facture est équilibrée', function () {
        $facture = Facture::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'posted',
            'montant_ht' => 5000,
            'montant_tva' => 1000,
            'montant_ttc' => 6000,
        ]);

        $customer = $facture->tiers;
        $customer->accountingAccount()->associate(
            AccountingAccounts::factory()->create([
                'tenant_id' => $this->tenant->id,
                'number' => '411001',
            ])
        )->save();

        $observer = app(FactureObserver::class);
        $observer->updated($facture);

        $entry = AccountingEntry::where('source_type', 'invoice')
            ->where('source_id', $facture->id)
            ->first();

        expect($entry->isBalanced())->toBeTrue();
    });
});

