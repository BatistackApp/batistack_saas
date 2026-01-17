<?php

namespace App\Observers\Core;

use App\Enums\Core\InvoiceStatus;
use App\Jobs\Core\PostPaymentJob;
use App\Jobs\Core\SyncStripeInvoiceJob;
use App\Models\Core\Invoice;
use App\Notifications\Core\InvoicePaidNotification;
use Illuminate\Support\Facades\Notification;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        // synchroniser / enrichir la facture asynchrone
        if ($invoice->status !== InvoiceStatus::Paid) {
            SyncStripeInvoiceJob::dispatch($invoice);
        }
    }

    public function updated(Invoice $invoice): void
    {
        // Si le statut a changé et est maintenant PAID -> actions post-paiement
        if ($invoice->wasChanged('status') && $invoice->status === InvoiceStatus::Paid->value) {
            PostPaymentJob::dispatch($invoice);

            $users = $invoice->tenant?->users ?? collect();

            if ($users->isNotEmpty()) {
                Notification::send($users, new InvoicePaidNotification($invoice));
            }
        }
    }

    public function deleted(Invoice $invoice): void {}
}
