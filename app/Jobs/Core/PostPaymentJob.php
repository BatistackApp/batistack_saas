<?php

namespace App\Jobs\Core;

use App\Enums\Core\InvoiceStatus;
use App\Models\Core\Invoice;
use App\Notifications\Core\InvoicePaidNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PostPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function handle(): void
    {
        $invoice = $this->invoice->fresh();
        if ($invoice === null) {
            \Log::warning('Invoice not found', ['id' => $this->invoice->id]);

            return;
        }

        // Ne traiter que les factures effectivement payées
        if ($invoice->status !== InvoiceStatus::Paid->value) {
            Log::debug('PostPaymentJob: invoice not in PAID status, skipping', [
                'invoice_id' => $invoice->id,
                'status' => $invoice->status,
            ]);

            return;
        }

        // Idempotence minimale : renseigner paid_at si absent
        if (empty($invoice->paid_at)) {
            $invoice->paid_at = now();
            $invoice->save();
        }

        // Notification des utilisateurs du tenant
        $users = $invoice->tenant?->users ?? collect();

        if ($users->isNotEmpty()) {
            Notification::send($users, new InvoicePaidNotification($invoice));
        }

        Log::info('PostPaymentJob: completed', ['invoice_id' => $invoice->id]);
    }
}
