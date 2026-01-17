<?php

namespace App\Services\Billing;

use App\Models\Core\Invoice;
use App\Models\Core\Tenant;
use Illuminate\Support\Facades\Log;

class InvoiceGeneratorService
{
    /**
     * Crée une facture pour un tenant (ex: fin de période d'essai, renouvellement).
     */
    public function generateForTenant(Tenant $tenant, array $data = []): Invoice
    {
        try {
            $invoice = Invoice::create([
                'tenant_id' => $tenant->id,
                'status' => $data['status'] ?? 'pending',
                'amount' => $data['amount'] ?? 0,
                'due_at' => $data['due_at'] ?? now()->addDays(30),
                'description' => $data['description'] ?? '',
            ]);

            Log::info('InvoiceGenerator: invoice created', [
                'invoice_id' => $invoice->id,
                'tenant_id' => $tenant->id,
                'amount' => $invoice->amount,
            ]);

            return $invoice;
        } catch (\Throwable $e) {
            Log::error('InvoiceGenerator: creation failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
